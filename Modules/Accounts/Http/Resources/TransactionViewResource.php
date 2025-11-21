<?php
namespace Modules\Accounts\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Accounts\Core\Helpers;
use Modules\Category\Http\Resources\CategoryResource;

class TransactionViewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'date'             => $this->date,
            'status'           => $this->status,
            'statusTranslated' => __('accounts::attributes.transactions.status.' . $this->status),
            'amount'           => $this->amount,
            'amountFormated'   => Helpers::formatMoneyWithSymbolAndCurrency($this->amount, $this->currencyCode, $this->currencySymbol),
            'description'      => $this->description,
            'type'             => $this->type,
            'userId'           => $this->userId,
            'userName'         => $this->userName,
            'accountId'        => $this->accountId,
            'accountName'      => $this->accountName,
            'currencySymbol'   => $this->currencySymbol,
            'currencyCode'     => $this->currencyCode,
            'category'         => new CategoryResource($this->category),
        ];
    }
}
