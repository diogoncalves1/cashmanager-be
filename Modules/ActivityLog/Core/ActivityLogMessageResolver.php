<?php
namespace Modules\ActivityLog\Core;

use Modules\Accounts\Core\Helpers;
use Modules\Accounts\Entities\Account;
use Modules\Category\Entities\Category;
use Modules\Currency\Entities\Currency;
use Modules\Debts\Entities\Debt;
use Modules\FinancialGoal\Entities\FinancialGoal;
use Modules\SharedRoles\Repositories\SharedRoleRepository;
use Modules\User\Repositories\UserRepository;

class ActivityLogMessageResolver
{
    protected $idsFields     = ['currency_id' => Currency::class, 'category_id' => Category::class];
    protected $logTypeModels = ['account' => Account::class, 'financial_goal' => FinancialGoal::class, 'debt' => Debt::class];

    public function __construct(
        protected SharedRoleRepository $sharedRoleRepo,
        protected UserRepository $userRepo
    ) {
    }

    public function resolve(string $logType, array $metadata): array
    {
        $metaType = $metadata['type'] ?? 'default';

        return [
            'key'    => "activitylog::messages.$logType.messages.$metaType",
            'params' => $this->resolveParams($metaType, $metadata, $logType),
        ];
    }

    protected function resolveParams(string $metaType, array $metadata, string $logType): array
    {
        $request = request();
        $user    = $request->user();

        return match ($metaType) {
            // ACCOUNTS
            'account_created'        => [
                'accountName' => $metadata['accountName'] ?? '',
            ],

            'account_updated'        => [
                'changes' => $this->formatChanges($metadata['changes'] ?? [], $logType),
            ],

            'account_status_updated' => [
                'status' => __("accounts::attributes.accounts.status." . ($metadata['status'] ? 'activated' : 'inactivated')),
            ],

            // TRANSACTIONS
            'transaction_added'      => [
                'type'   => __("accounts::attributes.transactions.type." . $metadata['transactionType']),
                'date'   => $metadata['date'],
                'amount' => $this->formatAmount($metadata['amount'], Account::find($metadata['accountId'])->currency_id, $metadata['amountFallback']),
            ],

            'transaction_scheduled'  => [
                'type'   => __("accounts::attributes.transactions.type." . $metadata['transactionType']),
                'date'   => $metadata['date'],
                'amount' => $this->formatAmount($metadata['amount'], Account::find($metadata['accountId'])->currency_id, $metadata['amountFallback']),
            ],

            'transaction_updated'    => [
                'changes' => $this->formatChanges($metadata['changes'] ?? [], $logType),
            ],

            'transaction_deleted'    => [
                'amount' => $this->formatAmount($metadata['amount'], Account::find($metadata['accountId'])->currency_id, $metadata['amountFallback']),
            ],

            'transaction_confirmed'  => [
                'amount' => $this->formatAmount($metadata['amount'], Account::find($metadata['accountId'])->currency_id, $metadata['amountFallback']),
                'date'   => $metadata['date'],
            ],

            // DEBTS
            'debt_created'           => [
                'debtName'      => $metadata['debtName'] ?? '',
                'initialAmount' => $this->formatAmount($metadata['initialAmount'], Debt::find($metadata['debtId'])->currency_id, $metadata['initialAmountFallback']),
            ],

            'debt_updated'           => [
                'changes' => $this->formatChanges($metadata['changes'], $logType),
            ],

            'debt_status_updated'    => [
                'status' => __("debts::attributes.debts.status." . ($metadata['status'])),
            ],

            // PAYMENTS
            'payment_added'          => [
                'date'               => $metadata['date'],
                'amount'             => $this->formatAmount($metadata['amount'], Debt::find($metadata['debtId'])->currency_id, $metadata['amountFallback']),
                'interest_rate'      => $metadata['interest_rate'],
                'is_monthly_payment' => __("debts::attributes.debt-payments.is-monthly-payment." . ($metadata['is_monthly_payment'] ? 'yes' : 'no')),
            ],

            'payment_scheduled'      => [
                'date'               => $metadata['date'],
                'amount'             => $this->formatAmount($metadata['amount'], Debt::find($metadata['debtId'])->currency_id, $metadata['amountFallback']),
                'interest_rate'      => $metadata['interest_rate'],
                'is_monthly_payment' => __("debts::attributes.debt-payments.is-monthly-payment." . ($metadata['is_monthly_payment'] ? 'yes' : 'no')),
            ],

            'payment_updated'        => [
                'changes' => $this->formatChanges($metadata['changes'] ?? [], $logType),
            ],

            'payment_deleted'        => [
                'amount' => $this->formatAmount($metadata['amount'], Account::find($metadata['debtId'])->currency_id, $metadata['amountFallback']),
                'date'   => $metadata['date'],
            ],

            'payment_confirmed'      => [
                'amount' => $this->formatAmount($metadata['amount'], Account::find($metadata['debtId'])->currency_id, $metadata['amountFallback']),
                'date'   => $metadata['date'],
            ],

            // FINANCIAL GOALS
            'goal_created'           => [
                'initialTarget' => $this->formatAmount($metadata['initialTarget'], FinancialGoal::find($metadata['goalId'])->currency_id, $metadata['initialTargetFallback']),
            ],

            'goal_updated'           => [
                'changes' => $this->formatChanges($metadata['changes'], $logType),
            ],

            'goal_status_updated'    => [
                'status' => __("financialgoal::attributes.financial-goals.status." . ($metadata['status'])),
            ],

            // FINANCIAL GOAL TRANSACTIONS
            'transaction_added'      => [
                'type'   => __("financialgoal::attributes.financial-goal-transactions.type." . $metadata['transactionType']),
                'date'   => $metadata['date'],
                'amount' => $this->formatAmount($metadata['amount'], Account::find($metadata['accountId'])->currency_id, $metadata['amountFallback']),
            ],

            'transaction_scheduled'  => [
                'type'   => __("financialgoal::attributes.financial-goal-transactions.type." . $metadata['transactionType']),
                'date'   => $metadata['date'],
                'amount' => $this->formatAmount($metadata['amount'], Account::find($metadata['accountId'])->currency_id, $metadata['amountFallback']),
            ],

            'transaction_updated'    => [
                'changes' => $this->formatChanges($metadata['changes'] ?? [], $logType),
            ],

            'transaction_deleted'    => [
                'amount' => $this->formatAmount($metadata['amount'], Account::find($metadata['accountId'])->currency_id, $metadata['amountFallback']),
            ],

            'transaction_confirmed'  => [
                'amount' => $this->formatAmount($metadata['amount'], Account::find($metadata['accountId'])->currency_id, $metadata['amountFallback']),
                'date'   => $metadata['date'],
            ],

            // MEMBERS
            'user_invited'           => [
                'userName' => $this->userRepo->show($metadata['invitedUserId'])->name,
                'roleName' => $this->sharedRoleRepo->show($metadata['sharedRoleId'])->name->{$user->preferences->lang},
            ],

            'user_joined'            => [
                'userName' => $this->userRepo->show($metadata['userId'])->name,
                'roleName' => $this->sharedRoleRepo->show($metadata['sharedRoleId'])->name->{$user->preferences->lang},
            ],

            'invited_destroyed'      => [
                'userName' => $this->userRepo->show($metadata['userId'])->name,
            ],

            'invited_revoked'        => [
                'userName' => $this->userRepo->show($metadata['userId'])->name,
            ],

            'user_revoked'           => [
                'userName' => $this->userRepo->show($metadata['userId'])->name,
            ],

            'user_role_updated'      => [
                'userName' => $this->userRepo->show($metadata['userId'])->name,
                'roleName' => $this->sharedRoleRepo->show($metadata['sharedRoleId'])->name->{$user->preferences->lang},
            ],

            'user_leaved'            => [
                'userName' => $this->userRepo->show($metadata['userId'])->name,
            ],

            default                  => [],
        };
    }

    protected function formatChanges(array $changes, string $logType): string
    {
        $messages = [];
        $request  = request();
        $user     = $request->user();

        foreach ($changes as $field => $change) {
            $old = $change['old'];
            $new = $change['new'];

            $model = new $this->logTypeModels[$logType];

            if (isset($change['formatAmount'])) {
                $old = $this->formatAmount($change['old'], $model->find($change['subjectId'])->currency_id, $change['oldFallback']);
                $new = $this->formatAmount($change['new'], $model->find($change['subjectId'])->currency_id, $change['newFallback']);
            }

            if (str_contains($field, 'id')) {
                $model    = new $this->idsFields[$field];
                $oldModel = $model->find($old);
                $newModel = $model->find($new);

                $old = $oldModel ? $oldModel->name->{$user->preferences->lang} : $change['oldFallback'];
                $new = $newModel ? $newModel->name->{$user->preferences->lang} : $change['newFallback'];
            }

            $messages[] = __('activitylog::messages.format-changes.support', [
                'field' => __('activitylog::fields.' . $field),
                'old'   => $old,
                'new'   => $new,
            ]);
        }

        return implode(', ', $messages);
    }

    protected function formatAmount(string $amount, string $currencyId, ?string $fallback): string
    {
        $currency = Currency::find($currencyId);

        if (! $currency) {
            return $fallback;
        }

        return Helpers::formatMoneyWithCurrency($amount, $currency->code, $currency->symbol);
    }
}
