<?php

namespace Modules\FinancialGoal\Http\Resources\Lists;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Accounts\Core\Helpers;
use Modules\FinancialGoal\Core\Helpers as CoreHelpers;
use Modules\FinancialGoal\Services\GoalReservations;
use Modules\User\Http\Resources\UserShareCollection;

class FinancialGoalListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $row = $this->resource;
        $user = $request->user();
        $data = $row->attributesToArray();
        $summary = GoalReservations::goalSummary($row->financialGoal);
        $data += $summary;
        $data['totalAmountFormated'] = Helpers::formatMoneyWithSymbolAndCurrency($row->totalAmount, $row->currencyCode, $row->currencySymbol);
        $data['contributedAmountFormated'] = Helpers::formatMoneyWithSymbolAndCurrency($row->contributedAmount, $row->currencyCode, $row->currencySymbol);
        $data['percentageCompeted'] = CoreHelpers::percentage($row->totalAmount, $row->contributedAmount);
        $data['priorityTranslated'] = __('financialgoal::attributes.financial-goals.priority.'.$row->priority);
        $data['statusTranslated'] = __('financialgoal::attributes.financial-goals.status.'.$row->status);
        $data['users'] = new UserShareCollection($row->users);
        $data['actions'] = $this->actions($row, $request);
        foreach (['userIds'] as $field) {
            unset($data[$field]);
        }
        foreach (['currencyId'] as $field) {
            unset($data[$field]);
        }
        foreach (['canceledAt'] as $field) {
            unset($data[$field]);
        }
        foreach (['completedAt'] as $field) {
            unset($data[$field]);
        }

        return $data;
    }

    private function actions($row, Request $request): mixed
    {
        $user = $request->user();

        $sharedRole = $row->userSharedRole($row, $user->id);

        $canView = $sharedRole?->hasPermission('viewFinancialGoal');
        $canEdit = $sharedRole?->hasPermission('updateFinancialGoal');
        $canDestroy = $sharedRole?->hasPermission('destroyFinancialGoal');
        $canManage = $sharedRole?->hasPermission('manageFinancialGoalUsers');
        $canMarkCompleted = $canEdit && $row->status == 'in_progress' && $row->contributedAmount >= $row->totalAmount;

        return ['view' => $canView, 'edit' => $canEdit, 'destroy' => $canDestroy, 'manage' => $canManage, 'markCompleted' => $canMarkCompleted];

    }
}
