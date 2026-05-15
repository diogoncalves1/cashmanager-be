<?php
namespace Modules\Notification\Actions\Notifications;

use Modules\Notification\Repositories\NotificationRepository;
use Modules\Notification\Services\NotificationCacheService;
use Modules\User\Entities\User;

class MarkAllAsReadAction
{
    public function __construct(protected NotificationRepository $repository, protected NotificationCacheService $service)
    {
    }

    public function execute(User $user): void
    {
        $this->service->forgetUnread($user->id);

        $this->repository->query()
            ->where('user_id', $user->id)
            ->unread()
            ->update([
                'read_at' => now(),
            ]);
    }
}
