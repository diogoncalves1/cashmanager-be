<?php
namespace Modules\FinancialGoal\Repositories;

use App\Repositories\RepositoryApiInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Core\Helpers;
use Modules\Debts\Core\Helpers as CoreHelpers;
use Modules\FinancialGoal\Entities\FinancialGoal;
use Modules\FinancialGoal\Entities\FinancialGoalBasicView;
use Modules\FinancialGoal\Entities\FinancialGoalTransaction;
use Modules\FinancialGoal\Entities\FinancialGoalTransactionView;
use Modules\FinancialGoal\Entities\FinancialGoalUser;
use Modules\FinancialGoal\Entities\FinancialGoalView;
use Modules\FinancialGoal\Exceptions\FinancialGoal\FinancialGoalInProgressException;
use Modules\FinancialGoal\Exceptions\FinancialGoal\FinancialGoalNotFullyFundedException;
use Modules\FinancialGoal\Exceptions\FinancialGoal\FinancialGoalNotInProgressException;
use Modules\FinancialGoal\Exceptions\FinancialGoal\UnauthorizedUpdateFinancialGoal;
use Modules\FinancialGoal\Exceptions\FinancialGoal\UnauthorizedViewFinancialGoal;
use Modules\FinancialGoal\Http\Resources\FinancialGoalTransactionViewCollection;
use Modules\SharedRoles\Entities\SharedRole;
use Modules\SharedRoles\Repositories\SharedRoleRepository;

class FinancialGoalRepository implements RepositoryApiInterface
{
    protected SharedRoleRepository $sharedRoleRepository;

    public function __construct(SharedRoleRepository $sharedRoleRepository)
    {
        $this->sharedRoleRepository = $sharedRoleRepository;
    }

    public function all()
    {
        return FinancialGoal::all();
    }

    public function allUser(Request $request)
    {
        $user = $request->user();

        return FinancialGoalBasicView::query()->whereRaw("FIND_IN_SET(?, REPLACE(user_ids, ' ', ''))", [$user->id])
            ->join("financial_goal_users", "financial_goal_users.financial_goal_id", '=', 'financial_goal_basic_view.id')
            ->join("shared_roles", "shared_roles.id", '=', "financial_goal_users.shared_role_id")
            ->join('shared_permission_roles', "shared_permission_roles.shared_role_id", '=', 'shared_roles.id')
            ->join('shared_permissions', "shared_permissions.id", '=', 'shared_permission_roles.shared_permission_id')
            ->where("financial_goal_users.user_id", $user->id)
            ->where('shared_permissions.code', 'createTransaction')
            ->status("in_progress")
            ->select("financial_goal_basic_view.*")->get();
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $user = $request->user();

            $input = $request->only(['name', 'total_amount', 'currency_id', 'start_date', 'due_date', 'description', 'priority']);

            $financialGoal = FinancialGoal::create($input);

            $inputUser = [
                'user_id'           => $user->id,
                'financial_goal_id' => $financialGoal->id,
                'shared_role_id'    => SharedRole::where('code', 'creator')->first()->id,
            ];

            FinancialGoalUser::create($inputUser);

            return $financialGoal;
        });
    }

    public function showToUser(Request $request, string $id)
    {
        $user = $request->user();

        $financialGoal = $this->show($id);

        $sharedRole = $financialGoal->userSharedRole($financialGoal, $user->id);

        if (! $sharedRole || ! $sharedRole->hasPermission('viewFinancialGoal')) {
            throw new UnauthorizedViewFinancialGoal();
        }

        $financialGoalView = $this->showView($id);

        return $financialGoalView;
    }

    public function showLastTransactions(string $id, int $limit = 3)
    {
        $financialGoal = $this->show($id);

        return $financialGoal->transactionsView()->orderBy('date', 'desc')->limit($limit)->get();
    }

    public function update(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = $request->user();

            $financialGoal = $this->show($id);

            $input = $request->only(['name', 'total_amount', 'currency_id', 'start_date', 'due_date', 'description', 'priority']);

            $sharedRole = $financialGoal->userSharedRole($financialGoal, $user->id);

            if (! $sharedRole || ! $sharedRole->hasPermission('updateFinancialGoal')) {
                throw new UnauthorizedUpdateFinancialGoal();
            }
            // TODO: Criar uma notificacao no frontend
            // if ($request->get('total_amount') < $financialGoal->contributed_amount) {
            // }

            $financialGoal->update($input);

            return $financialGoal;
        });
    }

    public function destroy(Request $request, string $id, )
    {
        return DB::transaction(function () use ($request, $id) {
            $user = $request->user();

            $financialGoal = $this->show($id);

            $sharedRole = $financialGoal->userSharedRole($financialGoal, $user->id);

            if (! $sharedRole || ! $sharedRole->hasPermission('destroyFinancialGoal')) {
                throw new UnauthorizedUpdateFinancialGoal();
            }

            $financialGoal->delete();

            return $financialGoal;
        });
    }

    public function cancel(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = $request->user();

            $financialGoal = $this->show($id);

            $sharedRole = $financialGoal->userSharedRole($financialGoal, $user->id);

            if (! $sharedRole || ! $sharedRole->hasPermission('updateFinancialGoal')) {
                throw new UnauthorizedUpdateFinancialGoal();
            }
            if ($financialGoal->status !== 'in_progress') {
                throw new FinancialGoalNotInProgressException();
            }
            $financialGoal->canceled_at = Carbon::now();
            $this->setStatus($financialGoal, 'canceled');

            return $financialGoal;
        });
    }

    public function complete(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = $request->user();

            $financialGoal = $this->show($id);

            $sharedRole = $financialGoal->userSharedRole($financialGoal, $user->id);

            if (! $sharedRole || ! $sharedRole->hasPermission('updateFinancialGoal')) {
                throw new UnauthorizedUpdateFinancialGoal();
            }
            if ($financialGoal->status !== 'in_progress') {
                throw new FinancialGoalNotInProgressException();
            }
            if ($financialGoal->contributed_amount < $financialGoal->total_amount) {
                throw new FinancialGoalNotFullyFundedException();
            }
            $financialGoal->completed_at = Carbon::now();
            $this->setStatus($financialGoal, 'completed');

            return $financialGoal;
        });
    }

    public function reset(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = $request->user();

            $financialGoal = $this->show($id);

            $sharedRole = $financialGoal->userSharedRole($financialGoal, $user->id);

            if (! $sharedRole || ! $sharedRole->hasPermission('updateFinancialGoal')) {
                throw new UnauthorizedUpdateFinancialGoal();
            }
            if ($financialGoal->status == 'in_progress') {
                throw new FinancialGoalInProgressException();
            }

            $financialGoal->canceled_at  = null;
            $financialGoal->completed_at = null;
            $this->setStatus($financialGoal, 'in_progress');

            return $financialGoal;
        });
    }

    public function show(string $id)
    {
        return FinancialGoal::findOrFail($id);
    }

    public function showMonthlyResume(int $id)
    {
        return FinancialGoalTransaction::query()
            ->join("financial_goals", "financial_goals.id", "=", "financial_goal_transactions.financial_goal_id")
            ->selectRaw("
                            CONCAT(MONTH(financial_goal_transactions.date), ' ', YEAR(financial_goal_transactions.date)) as monthYear,
                            SUM(
                                SUM(
                                    CASE
                                        WHEN financial_goal_transactions.type = 'contribution'
                                        THEN (financial_goal_transactions.amount)
                                        ELSE -(financial_goal_transactions.amount)
                                    END
                                )
                            ) OVER (ORDER BY MIN(financial_goal_transactions.date)) as balance
                        ")
            ->financialGoal($id)
            ->where('financial_goal_transactions.status', 'completed')
            ->groupByRaw("monthYear")
            ->orderByRaw("MIN(financial_goal_transactions.date)")
            ->get();

    }

    // Extra methods
    public function adjustContributedAmount(FinancialGoalTransaction $transaction): void
    {
        $financialGoal = $transaction->financialGoal;

        $financialGoal->contributed_amount += $transaction->type == 'contribution' ? $transaction->amount : -$transaction->amount;

        $financialGoal->save();
    }
    public function updateContributedAmount(FinancialGoalTransaction $transaction, float $difference): void
    {
        $financialGoal = $transaction->financialGoal;

        $financialGoal->contributed_amount -= $transaction->type == 'contribution' ? $difference : -$difference;

        $financialGoal->save();
    }
    public function reverseContributedAmount(FinancialGoalTransaction $transaction): void
    {
        $financialGoal = $transaction->financialGoal;

        $financialGoal->contributed_amount -= $transaction->type == 'contribution' ? $transaction->amount : -$transaction->amount;

        $financialGoal->save();
    }

    public function getStats(Request $request)
    {
        $user = $request->user();

        $currency = $user->preferences->currency;

        $totalQuery = FinancialGoalView::query()
            ->from('financial_goal_view as fg')
            ->join('currencies as goal_currency', 'fg.currencyId', '=', 'goal_currency.id')
            ->join('user_preferences', 'user_preferences.user_id', '=', DB::raw((int) $user->id))
            ->join('currencies as user_currency', 'user_currency.id', '=', 'user_preferences.currency_id')
            ->whereRaw("FIND_IN_SET(?, REPLACE(fg.userIds, ' ', ''))", [$user->id])
            ->selectRaw("
        SUM(fg.totalAmount * (user_currency.rate / goal_currency.rate)) as total_target,
        SUM(fg.contributedAmount * (user_currency.rate / goal_currency.rate)) as total_saved
    ")
            ->first();

        $stats = [
            'totalGoals'         => DB::table('financial_goal_view')
                ->whereRaw("FIND_IN_SET(?, REPLACE(userIds, ' ', ''))", [$user->id])
                ->count(),
            'activeGoals'        => DB::table('financial_goal_view')
                ->where('status', 'in_progress')
                ->whereRaw("FIND_IN_SET(?, REPLACE(userIds, ' ', ''))", [$user->id])
                ->count(),
            'totalTarget'        => $totalQuery->total_target,
            'totalSavedFormated' => Helpers::formatMoneyWithSymbolAndCurrency($totalQuery->total_saved ?? 0, $currency->code, $currency->symbol),
            'totalSaved'         => $totalQuery->total_saved,
        ];

        return $stats;
    }

    public function getFormStats(Request $request)
    {
        $user = $request->user();

        $currency = $user->preferences->currency;

        $totalQuery = FinancialGoalView::query()
            ->from('financial_goal_view as fg')
            ->join('currencies as goal_currency', 'fg.currencyId', '=', 'goal_currency.id')
            ->join('user_preferences', 'user_preferences.user_id', '=', DB::raw((int) $user->id))
            ->join('currencies as user_currency', 'user_currency.id', '=', 'user_preferences.currency_id')
            ->whereRaw("FIND_IN_SET(?, REPLACE(fg.userIds, ' ', ''))", [$user->id])
            ->selectRaw("
                SUM(fg.totalAmount * (user_currency.rate / goal_currency.rate)) as total_target,
                SUM(fg.contributedAmount * (user_currency.rate / goal_currency.rate)) as total_saved
            ")
            ->first();

        $statsValues = FinancialGoalTransaction::query()
            ->from('financial_goal_transactions as fgt')
            ->join('financial_goal_view as fg', 'fgt.financial_goal_id', '=', 'fg.id')
            ->join('currencies as goal_currency', 'fg.currencyId', '=', 'goal_currency.id')
            ->join('user_preferences', 'user_preferences.user_id', '=', DB::raw((int) $user->id))
            ->join('currencies as user_currency', 'user_currency.id', '=', 'user_preferences.currency_id')
            ->whereRaw("FIND_IN_SET(?, REPLACE(fg.userIds, ' ', ''))", [$user->id])
            ->selectRaw("
                    SUM(
                        CASE
                            WHEN YEAR(fgt.date) = YEAR(CURDATE())
                            AND MONTH(fgt.date) = MONTH(CURDATE())
                            AND fgt.status = 'completed'
                            THEN
                                CASE
                                    WHEN fgt.type = 'contribution'
                                    THEN fgt.amount * (user_currency.rate / goal_currency.rate)
                                    ELSE -(fgt.amount * (user_currency.rate / goal_currency.rate))
                                END
                            ELSE 0
                        END
                    ) as thisMonth,

                    SUM(
                        CASE
                            WHEN YEAR(fgt.date) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
                            AND MONTH(fgt.date) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
                            AND fgt.status = 'completed'
                            THEN
                                CASE
                                    WHEN fgt.type = 'contribution'
                                    THEN fgt.amount * (user_currency.rate / goal_currency.rate)
                                    ELSE -(fgt.amount * (user_currency.rate / goal_currency.rate))
                                END
                            ELSE 0
                        END
                    ) as lastMonth
            ")
            ->first();
        // FinancialGoalTransaction::where("user_id", $user->id)->whereRaw("YEAR(date) = YEAR(NOW())")->whereRaw("MONTH(date) = MONTH(NOW())")->sum('amount'),

        $stats = [
            'transactionSummary' => [
                'totalGoals'                   => DB::table('financial_goal_view')
                    ->whereRaw("FIND_IN_SET(?, REPLACE(userIds, ' ', ''))", [$user->id])
                    ->count(),
                'totalSaved'                   => Helpers::formatMoneyWithSymbolAndCurrency($totalQuery->total_saved ?? 0, $currency->code, $currency->symbol),
                'currentYearTotalTransactions' => FinancialGoalTransaction::where("user_id", $user->id)->whereRaw("YEAR(date) = YEAR(NOW())")->count(),
                'thisMonth'                    => Helpers::formatMoneyWithSymbolAndCurrency($statsValues->thisMonth ?? 0, $currency->code, $currency->symbol),
                'lastMonth'                    => Helpers::formatMoneyWithSymbolAndCurrency($statsValues->lastMonth ?? 0, $currency->code, $currency->symbol),
                'difLastMonth'                 => CoreHelpers::percentage($statsValues->lastMonth, $statsValues->thisMonth - $statsValues->lastMonth),
            ],
            'recentTransactions' => new FinancialGoalTransactionViewCollection(FinancialGoalTransactionView::where("userId", $user->id)->limit(3)->orderBy("date", "desc")->get()),
        ];

        return $stats;
    }

// Private methods
    private function showView(string $id)
    {
        $view = FinancialGoalView::where('id', $id)->first();

        if (! $view) {
            abort(404);
        }

        return $view;
    }
    private function setStatus(FinancialGoal $financialGoal, string $status)
    {
        $financialGoal->status = $status;

        $financialGoal->save();
    }
}
