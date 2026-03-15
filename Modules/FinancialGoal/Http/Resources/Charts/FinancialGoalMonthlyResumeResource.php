<?php
namespace Modules\FinancialGoal\Http\Resources\Charts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinancialGoalMonthlyResumeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'monthYear' => $this->monthYear,
            'balance'   => $this->balance,
        ];
    }
}
