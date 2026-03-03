<?php
namespace Modules\Debts\Repositories;

use App\Repositories\RepositoryApiInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Core\Helpers;
use Modules\ActivityLog\Repositories\ActivityLogRepository;
use Modules\Debts\Core\Helpers as CoreHelpers;
use Modules\Debts\Entities\Debt;
use Modules\Debts\Entities\DebtBasicView;
use Modules\Debts\Entities\DebtPayment;
use Modules\Debts\Entities\DebtPaymentView;
use Modules\Debts\Entities\DebtsView;
use Modules\Debts\Entities\DebtUser;
use Modules\Debts\Exceptions\Debts\DebtNotFoundException;
use Modules\Debts\Exceptions\Debts\DebtNotFullyPaidException;
use Modules\Debts\Exceptions\Debts\DebtNotPendingException;
use Modules\Debts\Exceptions\Debts\DebtPendingException;
use Modules\Debts\Exceptions\Debts\UnauthorizedDeleteDebt;
use Modules\Debts\Exceptions\Debts\UnauthorizedUpdateDebt;
use Modules\Debts\Exceptions\Debts\UnauthorizedViewDebt;
use Modules\Debts\Http\Resources\DebtPayments\DebtPaymentViewCollection;
use Modules\SharedRoles\Entities\SharedRole;

class DebtRepository implements RepositoryApiInterface
{
    public function __construct(protected ActivityLogRepository $activityRepo)
    {

    }

    public function all()
    {
        return Debt::all();
    }

    public function allUser(Request $request)
    {
        $user = $request->user();

        return DebtBasicView::query()->whereRaw("FIND_IN_SET(?, REPLACE(user_ids, ' ', ''))", [$user->id])
            ->join("debt_users", "debt_users.debt_id", '=', 'debt_basic_view.id')
            ->join("shared_roles", "shared_roles.id", '=', "debt_users.shared_role_id")
            ->join('shared_permission_roles', "shared_permission_roles.shared_role_id", '=', 'shared_roles.id')
            ->join('shared_permissions', "shared_permissions.id", '=', 'shared_permission_roles.shared_permission_id')
            ->where("debt_users.user_id", $user->id)
            ->where('shared_permissions.code', 'storeDebtPayment')
            ->status("pending")
            ->select("debt_basic_view.*")->get();
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $user  = $request->user();
            $input = $request->only(['currency_id', 'name', 'total_amount', 'months', 'interest_rate',
                'start_date', 'due_date', 'description', 'monthly_amount']);

            $debt = Debt::create($input);

            $debtUserInput = [
                'debt_id'        => $debt->id,
                'user_id'        => $user->id,
                'shared_role_id' => SharedRole::where("code", "creator")->first()->id,
            ];

            DebtUser::create($debtUserInput);
            $this->activityRepo->storeActivity($debt->id, $user->id, 'debt', ['type' => 'debt_created', 'initialAmount' => $input['total_amount'], 'currencyCode' => $debt->currency->code, 'currencySymbol' => $debt->currency->symbol]);

            return $debt;
        });
    }

    public function update(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $debt       = $this->show($id);
            $user       = $request->user();
            $sharedRole = $debt->userSharedRole($debt, $user->id);

            if (! $sharedRole->hasPermission('editDebt')) {
                throw new UnauthorizedUpdateDebt();
            }

            $input = $request->only(['currency_id', 'name', 'total_amount', 'months', 'interest_rate',
                'start_date', 'due_date', 'description', 'monthly_amount']);

            $debt->update($input);

            return $debt;
        });

    }

    public function destroy(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = $request->user();
            $debt = $this->show($id);

            $sharedRole = $debt->userSharedRole($debt, $user->id);

            if (! $sharedRole || ! $sharedRole->hasPermission('destroyDebt')) {
                throw new UnauthorizedDeleteDebt();
            }

            $debt->delete();

            return $debt;
        });
    }

    public function showToUser(Request $request, string $id)
    {
        $debt       = $this->show($id);
        $user       = $request->user();
        $sharedRole = $debt->userSharedRole($debt, $user->id);

        if (! $sharedRole->hasPermission('viewDebt')) {
            throw new UnauthorizedViewDebt();
        }

        $debtView = $this->showView($id);

        return $debtView;
    }

    public function markPaid(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $debt = $this->show($id);

            $user = $request->user();

            $sharedRole = $debt->userSharedRole($debt, $user->id);

            if (! $sharedRole || ! $sharedRole->hasPermission('editDebt')) {
                throw new UnauthorizedUpdateDebt();
            }
            if ($debt->status !== 'pending') {
                throw new DebtNotPendingException();
            }
            if ($debt->paid_amount < $debt->total_amount) {
                throw new DebtNotFullyPaidException();
            }

            $debt->paid_at = Carbon::now();
            $this->setStatus($debt, 'paid');

            return $debt;
        });
    }

    public function reset(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $debt = $this->show($id);

            $user = $request->user();

            $sharedRole = $debt->userSharedRole($debt, $user->id);

            if (! $sharedRole || ! $sharedRole->hasPermission('updateFinancialGoal')) {
                throw new UnauthorizedUpdateDebt();
            }
            if ($debt->status == 'pending') {
                throw new DebtPendingException();
            }

            $debt->paid_at = null;
            $this->setStatus($debt, 'pending');

            return $debt;
        });
    }

    public function show(string $id)
    {
        $debt = Debt::find($id);

        if (! $debt) {
            throw new DebtNotFoundException();
        }

        return $debt;
    }

    public function showView(string $id)
    {
        $debtView = DebtsView::find($id);

        if (! $debtView) {
            throw new DebtNotFoundException();
        }

        return $debtView;
    }

    public function adjustDebtForPaymentStore(DebtPayment $debtPayment)
    {
        $debt = $this->adjustPaidAmount($debtPayment);

        if ($debtPayment->is_monthly_payment) {
            $debt->increment('months_paid', 1);
        }
    }

    private function adjustPaidAmount(DebtPayment $debtPayment): Debt
    {
        $debt = $debtPayment->debt;

        $debt->paid_amount += $debtPayment->amount - ($debtPayment->amount * ($debtPayment->interest_rate / 100));

        $debt->save();
        return $debt;
    }
    public function updatePaidAmount(DebtPayment $debtPayment, float $oldAmount, float $amountToUpdate, float $oldInterestRate, float $currentInterestRate): void
    {
        $debt = $debtPayment->debt;

        $difference = ($oldAmount - ($oldAmount * ($oldInterestRate / 100))) - ($amountToUpdate - ($amountToUpdate * ($currentInterestRate / 100)));

        $debt->paid_amount -= $difference;

        $debt->save();
    }
    public function reversePaidAmount(DebtPayment $debtPayment): void
    {
        $debt = $debtPayment->debt;

        $debt->paid_amount -= $debtPayment->amount - ($debtPayment->amount * ($debtPayment->interest_rate / 100));

        $debt->save();
    }

    public function getFormStats(Request $request)
    {
        $user = $request->user();

        $currency = $user->preferences->currency;

        $totalQuery = DebtsView::query()
            ->from('debts_view as d')
            ->join('currencies as debt_currency', 'd.currencyId', '=', 'debt_currency.id')
            ->join('user_preferences', 'user_preferences.user_id', '=', DB::raw((int) $user->id))
            ->join('currencies as user_currency', 'user_currency.id', '=', 'user_preferences.currency_id')
            ->whereRaw("FIND_IN_SET(?, REPLACE(d.userIds, ' ', ''))", [$user->id])
            ->selectRaw("
                SUM(d.totalAmount * (user_currency.rate / debt_currency.rate)) as total_target,
                SUM(d.paidAmount * (user_currency.rate / debt_currency.rate)) as total_saved
            ")
            ->first();

        $statsValues = DebtPayment::query()
            ->from('debt_payments as dp')
            ->join('debts_view as d', 'dp.debt_id', '=', 'd.id')
            ->join('currencies as debt_currency', 'd.currencyId', '=', 'debt_currency.id')
            ->join('user_preferences', 'user_preferences.user_id', '=', DB::raw((int) $user->id))
            ->join('currencies as user_currency', 'user_currency.id', '=', 'user_preferences.currency_id')
            ->whereRaw("FIND_IN_SET(?, REPLACE(d.userIds, ' ', ''))", [$user->id])
            ->selectRaw("
                    SUM(
                        CASE
                            WHEN YEAR(dp.date) = YEAR(CURDATE())
                            AND MONTH(dp.date) = MONTH(CURDATE())
                            AND dp.status = 'completed'
                            THEN
                                dp.amount * (user_currency.rate / debt_currency.rate)
                            ELSE 0
                        END
                    ) as thisMonth,

                    SUM(
                        CASE
                            WHEN YEAR(dp.date) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
                            AND MONTH(dp.date) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
                            AND dp.status = 'completed'
                            THEN
                                dp.amount * (user_currency.rate / debt_currency.rate)
                            ELSE 0
                        END
                    ) as lastMonth
            ")
            ->first();
        // FinancialGoalTransaction::where("user_id", $user->id)->whereRaw("YEAR(date) = YEAR(NOW())")->whereRaw("MONTH(date) = MONTH(NOW())")->sum('amount'),

        $stats = [
            'transactionSummary' => [
                'totalDebts'                   => DB::table('debts_view')
                    ->whereRaw("FIND_IN_SET(?, REPLACE(userIds, ' ', ''))", [$user->id])
                    ->count(),
                'totalSaved'                   => Helpers::formatMoneyWithSymbolAndCurrency($totalQuery->total_saved ?? 0, $currency->code, $currency->symbol),
                'currentYearTotalTransactions' => DebtPayment::where("user_id", $user->id)->whereRaw("YEAR(date) = YEAR(NOW())")->count(),
                'thisMonth'                    => Helpers::formatMoneyWithSymbolAndCurrency($statsValues->thisMonth ?? 0, $currency->code, $currency->symbol),
                'lastMonth'                    => Helpers::formatMoneyWithSymbolAndCurrency($statsValues->lastMonth ?? 0, $currency->code, $currency->symbol),
                'difLastMonth'                 => CoreHelpers::percentage($statsValues->lastMonth ?? 0, $statsValues->thisMonth - $statsValues->lastMonth),
            ],
            'recentTransactions' => new DebtPaymentViewCollection(DebtPaymentView::where("userId", $user->id)->limit(3)->orderBy("date", "desc")->get()),
        ];

        return $stats;
    }

    public function getStats(Request $request): array
    {
        $user = $request->user();

        $currency = $user->preferences->currency;

        $totalQuery = DebtsView::query()
            ->from('debts_view as d')
            ->join('currencies as debt_currency', 'd.currencyId', '=', 'debt_currency.id')
            ->join('user_preferences', 'user_preferences.user_id', '=', DB::raw((int) $user->id))
            ->join('currencies as user_currency', 'user_currency.id', '=', 'user_preferences.currency_id')
            ->whereRaw("FIND_IN_SET(?, REPLACE(d.userIds, ' ', ''))", [$user->id])
            ->selectRaw("
        GREATEST(SUM(CASE WHEN d.status = 'pending' THEN (d.totalAmount - d.paidAmount) * (user_currency.rate / debt_currency.rate) ELSE 0 END), 0) as total_debt,
        SUM(d.totalAmount * (user_currency.rate / debt_currency.rate)) as total_amount_debt,
        SUM(d.paidAmount * (user_currency.rate / debt_currency.rate)) as total_paid,
        SUM(CASE WHEN d.status = 'pending' THEN d.monthlyAmount * (user_currency.rate / debt_currency.rate) ELSE 0 END) as monthly_payments
        ")
            ->first();

        $stats = [
            'paidDebts'         => DB::table('debts_view')
                ->where('status', 'paid')
                ->whereRaw("FIND_IN_SET(?, REPLACE(userIds, ' ', ''))", [$user->id])
                ->count(),
            'activeDebts'       => DB::table('debts_view')
                ->where('status', 'pending')
                ->whereRaw("FIND_IN_SET(?, REPLACE(userIds, ' ', ''))", [$user->id])
                ->count(),
            'totalDebt'         => $totalQuery->total_debt,
            'totalAmounDebt'    => $totalQuery->total_amount_debt,
            'totalDebtFormated' => Helpers::formatMoneyWithSymbolAndCurrency($totalQuery->total_debt ?? 0, $currency->code, $currency->symbol),
            'totalPaid'         => $totalQuery->total_paid,
            'totalPaidFormated' => Helpers::formatMoneyWithSymbolAndCurrency($totalQuery->total_paid ?? 0, $currency->code, $currency->symbol),
            'monthlyPayments'   => Helpers::formatMoneyWithSymbolAndCurrency($totalQuery->monthly_payments ?? 0, $currency->code, $currency->symbol),
        ];

        return $stats;
    }

    private function setStatus(Debt $debt, string $status)
    {
        $debt->status = $status;

        $debt->save();
    }
}
