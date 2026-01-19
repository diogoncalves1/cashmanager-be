<?php
namespace Modules\Debts\Repositories;

use App\Repositories\RepositoryApiInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Debts\Entities\Debt;
use Modules\Debts\Entities\DebtPayment;
use Modules\Debts\Entities\DebtsView;
use Modules\Debts\Entities\DebtUser;
use Modules\Debts\Exceptions\Debts\DebtNotFoundException;
use Modules\Debts\Exceptions\Debts\DebtNotFullyPaidException;
use Modules\Debts\Exceptions\Debts\DebtNotPendingException;
use Modules\Debts\Exceptions\Debts\DebtPendingException;
use Modules\Debts\Exceptions\Debts\UnauthorizedDeleteDebt;
use Modules\Debts\Exceptions\Debts\UnauthorizedUpdateDebt;
use Modules\Debts\Exceptions\Debts\UnauthorizedViewDebt;
use Modules\SharedRoles\Entities\SharedRole;

class DebtRepository implements RepositoryApiInterface
{
    public function all()
    {
        return Debt::all();
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

    public function adjustPaidAmount(DebtPayment $debtPayment): void
    {
        $debt = $debtPayment->debt;

        $debt->paid_amount += $debtPayment->amount - ($debtPayment->amount * ($debtPayment->interest_rate / 100));

        $debt->save();
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

    private function setStatus(Debt $debt, string $status)
    {
        $debt->status = $status;

        $debt->save();
    }
}
