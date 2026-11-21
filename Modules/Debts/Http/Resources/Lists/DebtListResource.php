<?php

namespace Modules\Debts\Http\Resources\Lists;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Accounts\Core\Helpers;
use Modules\Debts\Core\Helpers as DebtHelpers;
use Modules\User\Http\Resources\UserShareCollection;

class DebtListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $row = $this->resource;
        $user = $request->user();
        $data = $row->attributesToArray();
        $data['interestValue'] = DebtHelpers::debtInterestValue($row->totalAmount, $row->interestRate, $row->months ?? 0);
        $data['remainingAmount'] = DebtHelpers::debtRemainingAmount($row->totalAmount, $row->paidAmount, $row->interestRate, $row->months);
        $data['totalAmountWithInterest'] = DebtHelpers::debtTotalAmount($row->totalAmount, $row->interestRate, $row->months);
        $data['totalAmountFormated'] = Helpers::formatMoneyWithSymbolAndCurrency($row->totalAmount, $row->currencyCode, $row->currencySymbol);
        $data['totalAmountFormatedWithoutSymbol'] = Helpers::formatMoneyWithCurrency($row->totalAmount, $row->currencyCode, $row->currencySymbol);
        $data['paidAmountFormated'] = Helpers::formatMoneyWithSymbolAndCurrency($row->paidAmount, $row->currencyCode, $row->currencySymbol);
        $data['monthlyAmountFormated'] = Helpers::formatMoneyWithSymbolAndCurrency($row->monthlyAmount, $row->currencyCode, $row->currencySymbol);
        $data['statusTranslated'] = __('debts::attributes.debts.status.'.$row->status);
        $data['users'] = new UserShareCollection($row->users);
        $data['actions'] = $this->actions($row, $request);
        foreach (['user_ids', 'userIds'] as $field) {
            unset($data[$field]);
        }

        return $data;
    }

    private function actions($row, Request $request): mixed
    {
        $user = $request->user();

        $debt = $row->debt;
        $sharedRole = $debt->userSharedRole($debt, $user->id);

        $canView = $sharedRole?->hasPermission('viewDebt');

        return ['view' => (bool) $canView];

    }
}
