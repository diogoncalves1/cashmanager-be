<?php

namespace Modules\RecurringTransaction\Http\Resources\Lists;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Accounts\Core\Helpers;
use Modules\Category\Http\Resources\CategoryResource;

class RecurringTransactionListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $row = $this->resource;
        $user = $request->user();
        $data = $row->attributesToArray();
        $data['amount'] = (float) $row->amount;
        $data['amountFormated'] = Helpers::formatMoneyWithSymbolAndCurrency($row->amount, $row->account->currency->code, $row->account->currency->symbol);
        $data['lastGenerated'] = $row->last_generated_at ? $row->last_generated_at->format('Y-m-d') : null;
        $data['nextOccurrence'] = $row->next_occurrence;
        $data['currencySymbol'] = $row->account->currency->symbol;
        $data['currencyCode'] = $row->account->currency->code;
        $data['isActive'] = (bool) $row->is_active;
        $data['customInterval'] = $row->custom_frequency_days;
        $data['categoryName'] = $row->category ? (new CategoryResource($row->category))->resolve($request)['name'] : null;
        $data['categoryIcon'] = $row->category?->icon;
        $data['categoryColor'] = $row->category?->color;
        $data['accountName'] = $row->account->name;
        $data['accountType'] = $row->account->type;
        $data['actions'] = $this->actions($row, $request);
        $data['isLinked'] = (bool) $row->is_linked;
        $data['linkedName'] = $row->is_linked ? $row->linked?->name : null;
        $data['linked'] = $row->is_linked && $row->linked ? ['id' => $row->linked->id, 'name' => $row->linked->name] : null;
        $data['linkedType'] = $row->is_linked ? ['Modules\\Debts\\Entities\\Debt' => 'debt', 'Modules\\FinancialGoal\\Entities\\FinancialGoal' => 'financial_goal'][$row->linked_type] : null;
        $data['userName'] = $row->user->name;
        $data['category'] = $row->category ? new CategoryResource($row->category) : null;
        foreach (['account_id', 'account', 'user', 'category_id', 'user_id', 'linked_type', 'linked_id', 'created_at', 'updated_at', 'deleted_at', 'is_linked', 'last_generated_at', 'is_active', 'end_date', 'custom_frequency_days', 'start_date'] as $field) {
            unset($data[$field]);
        }

        return $data;
    }

    private function actions($row, Request $request): mixed
    {
        $user = $request->user();

        return [
            'view' => $user->can('show', $row),
            'edit' => $user->can('update', $row),
            'toggle' => $user->can('update', $row),
            'destroy' => $user->can('delete', $row),
        ];

    }
}
