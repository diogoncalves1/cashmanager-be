<?php

namespace Modules\Debts\Http\Resources\Lists;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Accounts\Core\Helpers;

class DebtPaymentListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $row = $this->resource;
        $user = $request->user();
        $data = $row->attributesToArray();
        $data['statusTranslated'] = __('accounts::attributes.transactions.status.'.$row->status);
        $data['amountFormated'] = Helpers::formatMoneyWithSymbolAndCurrency($row->amount, $row->currencyCode, $row->currencySymbol);
        $data['interestPaidFormated'] = Helpers::formatMoneyWithSymbolAndCurrency($row->interestPaid, $row->currencyCode, $row->currencySymbol);
        $data['actions'] = $this->actions($row, $request);

        return $data;
    }

    private function actions($row, Request $request): mixed
    {
        $user = $request->user();

        $debt = $row->debt;
        $sharedRole = $debt->userSharedRole($debt, $user->id);

        $canConfirm = $row->status == 'pending' && $row->date <= date('Y-m-d') && $sharedRole?->hasPermission('confirmDebtPayment');
        $canView = $sharedRole?->hasPermission('viewDebtPayment');
        $canEdit = $sharedRole?->hasPermission('editDebtPayment');
        $canDestroy = $sharedRole?->hasPermission('destroyDebtPayment');

        return ['view' => $canView, 'confirm' => $canConfirm, 'edit' => $canEdit, 'destroy' => $canDestroy];

    }
}
