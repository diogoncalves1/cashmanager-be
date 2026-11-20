<?php

namespace Modules\FinancialGoal\Http\Resources\Lists;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Accounts\Core\Helpers;

class FinancialGoalTransactionListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $row = $this->resource;
        $user = $request->user();
        $data = $row->attributesToArray();
        $data['amountFormated'] = Helpers::formatMoneyWithSymbolAndCurrency($row->amount, $row->currencyCode, $row->currencySymbol);
        $data['balanceBefore'] = Helpers::formatMoneyWithSymbolAndCurrency(($row->balanceBefore ?? 0), $row->currencyCode, $row->currencySymbol);
        $data['balanceAfter'] = Helpers::formatMoneyWithSymbolAndCurrency(($row->balanceBefore ?? 0) + ($row->status === 'completed' ? ($row->type === 'contribution' ? $row->amount : -$row->amount) : 0), $row->currencyCode, $row->currencySymbol);
        $data['typeTranslated'] = __('financialgoal::attributes.financial-goal-transactions.type.'.$row->type);
        $data['statusTranslated'] = __('financialgoal::attributes.financial-goal-transactions.status.'.$row->status);
        $data['actions'] = $this->actions($row, $request);
        foreach (['currencyId'] as $field) {
            unset($data[$field]);
        }
        foreach (['userId'] as $field) {
            unset($data[$field]);
        }

        return $data;
    }

    private function actions($row, Request $request): mixed
    {
        $user = $request->user();

        $sharedRole = $row->financialGoal->userSharedRole($row->financialGoal, $user->id);

        $canView = $sharedRole?->hasPermission('viewFinancialGoalTransaction');
        $canEdit = $sharedRole?->hasPermission('updateFinancialGoalTransaction');
        $canDestroy = $sharedRole?->hasPermission('destroyFinancialGoalTransaction');
        $canConfirm = ($row->status == 'pending' && $row->date < Carbon::now()) && $sharedRole?->hasPermission('confirmScheduledFinancialGoalTransactions');

        return ['view' => $canView, 'edit' => $canEdit && ($row->status === 'pending'), 'destroy' => $canDestroy && ($row->status === 'pending' || $row->type === 'usage'), 'confirm' => $canConfirm];

    }
}
