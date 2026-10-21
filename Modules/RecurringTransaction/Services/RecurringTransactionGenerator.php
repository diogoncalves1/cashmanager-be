<?php

namespace Modules\RecurringTransaction\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Accounts\Repositories\TransactionRepository;
use Modules\Debts\Entities\Debt;
use Modules\Debts\Repositories\DebtPaymentRepository;
use Modules\FinancialGoal\Entities\FinancialGoal;
use Modules\FinancialGoal\Repositories\FinancialGoalTransactionRepository;
use Modules\RecurringTransaction\Entities\RecurringTransaction;

class RecurringTransactionGenerator
{
    public function __construct(
        protected TransactionRepository $transactionRepo,
        protected DebtPaymentRepository $debtPaymentRepo,
        protected FinancialGoalTransactionRepository $financialGoalTransactionRepo,
    ) {}

    /**
     * Generate all pending transactions for a recurring entry.
     * Returns the number of transactions created.
     */
    public function generate(RecurringTransaction $recurring): int
    {
        if (! $recurring->is_active) {
            return 0;
        }

        $previousUser = Auth::user();
        $user = $recurring->user;

        Auth::setUser($user);

        try {
            $generated = 0;
            $nextDate = $this->nextPendingDate($recurring);

            while ($this->shouldGenerate($recurring, $nextDate)) {
                $this->createTransaction($recurring, $nextDate);

                $recurring->update(['last_generated_at' => $nextDate->toDateString()]);

                $generated++;
                $nextDate = $recurring->nextDateAfter($nextDate);
            }

            // Automatically disable if expired
            if ($recurring->isExpired()) {
                $recurring->update(['is_active' => false]);
            }

            return $generated;
        } finally {
            if ($previousUser) {
                Auth::setUser($previousUser);
            } else {
                Auth::guard()->forgetUser();
            }
        }
    }

    private function nextPendingDate(RecurringTransaction $recurring): Carbon
    {
        // If it has never generated before → start from start_date
        if (! $recurring->last_generated_at) {
            return $recurring->start_date->copy();
        }

        return $recurring->nextDateAfter($recurring->last_generated_at);
    }

    private function shouldGenerate(RecurringTransaction $recurring, Carbon $date): bool
    {
        if ($date->isFuture()) {
            return false;
        }

        if ($recurring->end_date && $date->gt($recurring->end_date)) {
            return false;
        }

        return true;
    }

    private function createTransaction(RecurringTransaction $recurring, Carbon $date): Model
    {
        if (! $recurring->is_linked) {
            return $this->transactionRepo->store($this->makeRequest($recurring, $date, [
                'category_id' => $recurring->category_id,
                'type' => $recurring->type,
            ]));
        }

        return match ($recurring->linked_type) {
            Debt::class => $this->createDebtPayment($recurring, $date),
            FinancialGoal::class => $this->createFinancialGoalTransaction($recurring, $date),
        };
    }

    private function createDebtPayment(RecurringTransaction $recurring, Carbon $date): Model
    {
        return $this->debtPaymentRepo->store($this->makeRequest($recurring, $date, [
            'debt_id' => $recurring->linked_id,
            'interest_rate' => 0,
            'is_monthly_payment' => false,
        ]));
    }

    private function createFinancialGoalTransaction(RecurringTransaction $recurring, Carbon $date): Model
    {
        return $this->financialGoalTransactionRepo->store($this->makeRequest($recurring, $date, [
            'financial_goal_id' => $recurring->linked_id,
            'type' => $this->financialGoalTransactionType($recurring),
        ]));
    }

    private function financialGoalTransactionType(RecurringTransaction $recurring): string
    {
        return $recurring->type === 'expense' ? 'contribution' : 'withdrawal';
    }

    private function makeRequest(RecurringTransaction $recurring, Carbon $date, array $overrides = []): Request
    {
        $request = new Request([
            ...[
                'account_id' => $recurring->account_id,
                'amount' => $recurring->amount,
                'description' => $recurring->description ?? $recurring->title,
                'date' => $date->toDateString(),
                'is_recurring' => true,
                'recurring_transaction_id' => $recurring->id,
                'status' => 'completed',
            ],
            ...$overrides,
        ]);

        $request->setUserResolver(fn () => $recurring->user);

        return $request;
    }
}
