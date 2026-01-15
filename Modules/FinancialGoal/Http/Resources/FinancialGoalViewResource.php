<?php
namespace Modules\FinancialGoal\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Accounts\Core\Helpers;
use Modules\FinancialGoal\Core\Helpers as CoreHelpers;

class FinancialGoalViewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        foreach ($this->users as &$user) {
            $user->sharedRole = $user->pivot->sharedRole;
        }

        $missingAmount = $this->totalAmount - $this->contributedAmount;

        $user       = $request->user();
        $sharedRole = $this->financialGoal->userSharedRole($this->financialGoal, $user->id);

        $canEdit               = $sharedRole?->hasPermission("updateFinancialGoal");
        $canDestroy            = $sharedRole?->hasPermission("destroyFinancialGoal");
        $canManage             = $sharedRole?->hasPermission("manageFinancialGoalUsers");
        $canCreateTransactions = $sharedRole?->hasPermission("storeFinancialGoalTransaction");

        $actions = ['edit' => $canEdit, 'destroy' => $canDestroy, 'manage' => $canManage, 'createTransactions' => $canCreateTransactions];

        return [
            'id'                                     => $this->id,
            'name'                                   => $this->name,
            'status'                                 => $this->status,
            'statusTranslated'                       => __('financialgoal::attributes.financial-goals.status.' . $this->status),
            'priority'                               => $this->priority,
            'priorityTranslated'                     => __('financialgoal::attributes.financial-goals.priority.' . $this->priority),

            'totalAmount'                            => $this->totalAmount,
            'totalAmountFormated'                    => Helpers::formatMoneyWithSymbolAndCurrency($this->totalAmount, $this->currencyCode, $this->currencySymbol),
            'totalAmountFormatedWithoutSymbol'       => Helpers::formatMoneyWithCurrency($this->totalAmount, $this->currencyCode, $this->currencySymbol),

            'contributedAmount'                      => $this->contributedAmount,
            'contributedAmountFormatedWithoutSymbol' => Helpers::formatMoneyWithCurrency($this->contributedAmount, $this->currencyCode, $this->currencySymbol),
            'contributedAmountFormated'              => Helpers::formatMoneyWithSymbolAndCurrency($this->contributedAmount, $this->currencyCode, $this->currencySymbol),

            'currencySymbol'                         => $this->currencySymbol,
            'currencyCode'                           => $this->currencyCode,
            'currencyId'                             => $this->currencyId,

            'startDate'                              => $this->startDate,
            'dueDate'                                => $this->dueDate,
            'userNames'                              => $this->userNames,
            'description'                            => $this->description,

            'missingAmountFormated'                  => Helpers::formatMoneyWithCurrency($missingAmount, $this->currencyCode, $this->currencySymbol),
            'missingAmount'                          => $missingAmount,

            'percentageCompleted'                    => CoreHelpers::percentage($this->totalAmount, $this->contributedAmount),

            'totalContributions'                     => $this->totalContributions,
            'totalContributionsFormated'             => Helpers::formatMoneyWithCurrency($this->totalContributions, $this->currencyCode, $this->currencySymbol),

            'totalWithdrawals'                       => $this->totalWithdrawals,
            'totalWithdrawalsFormated'               => Helpers::formatMoneyWithCurrency($this->totalWithdrawals, $this->currencyCode, $this->currencySymbol, true),

            'users'                                  => new \Modules\User\Http\Resources\UserShareCollection($this->users),
            'totalTransactions'                      => $this->totalTransactions,
            'totalContributions'                     => $this->totalContributions,

            'completedAt'                            => $this->completedAt,
            'canceledAt'                             => $this->canceledAt,
            'updatedAt'                              => $this->updatedAt,
            'createdAt'                              => $this->createdAt,

            'actions'                                => $actions,
        ];
    }
}
