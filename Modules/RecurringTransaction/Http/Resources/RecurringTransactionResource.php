<?php

namespace Modules\RecurringTransaction\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Accounts\Http\Resources\AccountResource;
use Modules\Accounts\Http\Resources\TransactionCollection;
use Modules\Category\Http\Resources\CategoryResource;
use Modules\Debts\Http\Resources\DebtResource;
use Modules\FinancialGoal\Http\Resources\FinancialGoalResource;
use Modules\RecurringTransaction\Core\ResolveRecurringTransactionLinked;

class RecurringTransactionResource extends JsonResource
{
    protected $linkedResources = [
        'Modules\Debts\Entities\Debt' => DebtResource::class,
        'Modules\FinancialGoal\Entities\FinancialGoal' => FinancialGoalResource::class,
    ];

    protected $linkedTypeMap = [
        'Modules\Debts\Entities\Debt' => 'debt',
        'Modules\FinancialGoal\Entities\FinancialGoal' => 'financial_goal',
    ];

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'amount' => (float) $this->amount,
            'nextOccurrence' => $this->next_occurrence,
            'description' => $this->description,
            'frequency' => $this->frequency,
            'customFrequencyDays' => $this->when(
                $this->frequency === 'custom',
                $this->custom_frequency_days
            ),
            'startDate' => $this->start_date?->toDateString(),
            'endDate' => $this->end_date?->toDateString(),
            'lastGeneratedAt' => $this->last_generated_at?->toDateString(),
            'isActive' => $this->is_active,

            'isLinked' => $this->is_linked,
            'linkedType' => $this->linked_type ? $this->linkedTypeMap[$this->linked_type] : null,
            'linked' => ResolveRecurringTransactionLinked::resolve($this->resource),

            'account' => new AccountResource($this->whenLoaded('account')),
            'category' => new CategoryResource($this->whenLoaded('category')),

            'transactions_count' => $this->whenCounted('transactions'),
            'transactions' => new TransactionCollection($this->whenLoaded('transactions')),
        ];
    }
}
