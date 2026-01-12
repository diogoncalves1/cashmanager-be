<?php
namespace Modules\FinancialGoal\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Accounts\Core\Helpers;
use Modules\FinancialGoal\Core\Helpers as CoreHelpers;

class UserFinancialGoalContributionViewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'userId'                   => $this->userId,
            'goalName'                 => $this->goalName,
            'goalId'                   => $this->goalId,
            'userName'                 => $this->userName,
            'currencySymbol'           => $this->currencySymbol,
            'currencyCode'             => $this->currencyCode,
            'totalContributed'         => $this->totalContributed,
            'totalContributedFormated' => Helpers::formatMoneyWithSymbolAndCurrency($this->totalContributed, $this->currencyCode, $this->currencySymbol),
            'percentageContributed'    => CoreHelpers::percentage($this->financialGoal->total_amount, $this->totalContributed),
        ];
    }
}
