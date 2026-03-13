<?php
namespace Modules\ActivityLog\Core;

use Modules\Accounts\Core\Helpers;
use Modules\Currency\Entities\Currency;
use Modules\SharedRoles\Repositories\SharedRoleRepository;
use Modules\User\Repositories\UserRepository;

class ActivityLogMessageResolver
{
    protected $idsFields = ['currency_id' => Currency::class];

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
            'params' => $this->resolveParams($metaType, $metadata),
        ];
    }

    protected function resolveParams(string $metaType, array $metadata): array
    {
        $request = request();
        $user    = $request->user();

        return match ($metaType) {
            'account_created'        => [
                'accountName' => $metadata['accountName'] ?? '',
            ],

            'account_updated'        => [
                'changes' => $this->formatChanges($metadata['changes'] ?? []),
            ],

            'account_status_updated' => [
                'status' => __("accounts::attributes.accounts.status." . ($metadata['status'] ? 'activated' : 'inactivated')),
            ],

            'goal_created'           => [
                'initialTarget' => Helpers::formatMoneyWithCurrency($metadata['initialTarget'] ?? 0, $metadata['currencyCode'], $metadata['currencySymbol']),
            ],
            'contribution_added'     => [
                'amount'      => Helpers::formatMoneyWithCurrency($metadata['amount'] ?? 0, $metadata['currencyCode'], $metadata['currencySymbol']),
                'accountName' => $metadata['accountName'],
            ],
            'debt_created'           => [
                'initialAmount' => Helpers::formatMoneyWithCurrency($metadata['initialAmount'] ?? 0, $metadata['currencyCode'], $metadata['currencySymbol']),
            ],

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

    protected function formatChanges(array $changes): string
    {
        $messages = [];
        $request  = request();
        $user     = $request->user();

        foreach ($changes as $field => $change) {
            $old = $change['old'];
            $new = $change['new'];

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
}
