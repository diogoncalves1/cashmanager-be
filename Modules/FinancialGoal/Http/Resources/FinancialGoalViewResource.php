<?php
namespace Modules\FinancialGoal\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Accounts\Core\Helpers;
use Modules\FinancialGoal\Core\Helpers as CoreHelpers;

class FinancialGoalViewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        foreach ($this->users as &$user) {
            $user->sharedRole = $user->pivot->sharedRole;
        }
        return [
            'id'                       => $this->id,
            'name'                     => $this->name,
            'status'                   => $this->status,
            'statusTranslated'         => __('financialgoal::attributes.financial-goals.status.' . $this->status),
            'priority'                 => $this->priority,
            'priorityTranslated'       => __('financialgoal::attributes.financial-goals.priority.' . $this->priority),
            'totalAmount'              => $this->totalAmount,
            'totalAmountFormated'      => Helpers::formatMoneyWithSymbolAndCurrency($this->totalAmount, $this->currencyCode, $this->currencySymbol),
            'contributedAmount'        => $this->contributedAmount,
            'contributedAmoutFormated' => Helpers::formatMoneyWithSymbolAndCurrency($this->contributedAmount, $this->currencyCode, $this->currencySymbol),
            'currencySymbol'           => $this->currencySymbol,
            'currencyId'               => $this->currencyId,
            'startDate'                => $this->startDate,
            'dueDate'                  => $this->dueDate,
            'userNames'                => $this->userNames,
            'description'              => $this->description,
            'percentageCompeted'       => CoreHelpers::percentage($this->totalAmount, $this->contributedAmount),
            'users'                    => new \Modules\User\Http\Resources\UserShareCollection($this->users),
            'completedAt'              => $this->whenNotNull('completedAt'),
            'canceledAt'               => $this->whenNotNull('canceledAt'),
        ];
    }
}
