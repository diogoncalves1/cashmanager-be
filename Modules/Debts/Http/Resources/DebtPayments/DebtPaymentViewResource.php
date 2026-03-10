<?php
namespace Modules\Debts\Http\Resources\DebtPayments;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Accounts\Core\Helpers;

class DebtPaymentViewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {

        return [
            'id'               => $this->id,
            'status'           => $this->status,
            'statusTranslated' => __('debt::attributes.debt-payments.status.' . $this->status),
            'amount'           => $this->amount,
            'amountFormated'   => Helpers::formatMoneyWithSymbolAndCurrency($this->amount, $this->currencyCode, $this->currencySymbol),
            'date'             => $this->date,
            'description'      => $this->description,
            'userName'         => $this->userName,
            'debtName'         => $this->debtName,
            'debtId'           => $this->debtId,
            'interestRate'     => $this->interestRate,
            'isMonthlyPayment' => $this->isMonthlyPayment,
            'accountId'        => (string) $this->accountId,
            'currencySymbol'   => $this->currencySymbol,
            'currencyId'       => $this->currencyId,
            'accountName'      => $this->accountName,
        ];
    }
}
