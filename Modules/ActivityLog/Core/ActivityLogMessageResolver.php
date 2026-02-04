<?php
namespace Modules\ActivityLog\Core;

use Modules\Accounts\Core\Helpers;
use Modules\SharedRoles\Repositories\SharedRoleRepository;
use Modules\User\Repositories\UserRepository;

class ActivityLogMessageResolver
{
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
            'goal_created' => [
                'initialTarget' => Helpers::formatMoneyWithCurrency($metadata['initialTarget'] ?? 0, $metadata['currencyCode'], $metadata['currencySymbol']),
            ],

            'user_invited' => [
                'userName' => $this->userRepo->show($metadata['invitedUserId'])->name,
                'roleName' => $this->sharedRoleRepo->show($metadata['sharedRoleId'])->name->{$user->preferences->lang},
            ],

            default        => [],
        };
    }
}
