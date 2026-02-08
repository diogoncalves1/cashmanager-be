<?php
namespace Modules\Debts\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Accounts\Core\Helpers;
use Modules\Debts\Core\Helpers as CoreHelpers;

class DebtViewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);

        foreach ($this->userPayments as &$user) {
            $user->sharedRole     = $user->pivot->sharedRole;
            $user->totalPaid      = $user->pivot->totalPaid;
            $user->currencyCode   = $user->pivot->currencyCode;
            $user->currencySymbol = $user->pivot->currencySymbol;
        }

        $user       = $request->user();
        $sharedRole = $this->debt->userSharedRole($this->debt, $user->id);

        $canEdit               = $sharedRole?->hasPermission("editDebt");
        $canDestroy            = $sharedRole?->hasPermission("destroyDebt");
        $canManage             = $sharedRole?->hasPermission("manageDebtUsers");
        $canCreateTransactions = $sharedRole?->hasPermission("storeDebtPayment");

        $actions = ['edit' => $canEdit, 'destroy' => $canDestroy, 'manage' => $canManage, 'createTransactions' => $canCreateTransactions];

        $missingAmount = $this->totalAmount - $this->paidAmount;

        return [
            'id'                               => $this->id,
            'name'                             => $this->name,
            'status'                           => $this->status,
            'statusTranslated'                 => __('debts::attributes.debts.status.' . $this->status),

            'totalAmount'                      => $this->totalAmount,
            'totalAmountFormated'              => Helpers::formatMoneyWithSymbolAndCurrency($this->totalAmount, $this->currencyCode, $this->currencySymbol),
            'totalAmountFormatedWithoutSymbol' => Helpers::formatMoneyWithCurrency($this->totalAmount, $this->currencyCode, $this->currencySymbol),

            'paidAmount'                       => $this->paidAmount,
            'paidAmountFormatedWithoutSymbol'  => Helpers::formatMoneyWithCurrency($this->paidAmount, $this->currencyCode, $this->currencySymbol),
            'paidAmountFormated'               => Helpers::formatMoneyWithSymbolAndCurrency($this->paidAmount, $this->currencyCode, $this->currencySymbol),

            'currencySymbol'                   => $this->currencySymbol,
            'currencyCode'                     => $this->currencyCode,
            'currencyId'                       => $this->currencyId,

            'startDate'                        => $this->startDate,
            'dueDate'                          => $this->dueDate,
            'userNames'                        => $this->userNames,
            'description'                      => $this->description,

            'interestPaidFormated'             => Helpers::formatMoneyWithCurrency($this->interestPaid, $this->currencyCode, $this->currencySymbol),
            'interestPaid'                     => $this->interestPaid,

            'missingAmountFormated'            => Helpers::formatMoneyWithCurrency($missingAmount, $this->currencyCode, $this->currencySymbol),
            'missingAmount'                    => $missingAmount,

            'percentageCompleted'              => CoreHelpers::percentage($this->totalAmount, $this->paidAmount),

            'totalPayments'                    => $this->totalPayments,
            'totalPaymentsFormated'            => Helpers::formatMoneyWithCurrency($this->totalPayments, $this->currencyCode, $this->currencySymbol),

            'users'                            => new \Modules\Debts\Http\Resources\DebtPayments\UserDebtPaymentViewCollection($this->userPayments),

            'totalTransactions'                => $this->totalTransactions,

            'months'                           => $this->months,
            'monthsPaid'                       => $this->monthsPaid,
            'interestRate'                     => $this->interestRate,
            'paidAt'                           => $this->paidAt,
            'monthlyAmount'                    => $this->monthlyAmount,
            'monthlyAmountFormated'            => Helpers::formatMoneyWithCurrency($this->monthlyAmount, $this->currencyCode, $this->currencySymbol),

            'actions'                          => $actions,

        ];
    }
}
