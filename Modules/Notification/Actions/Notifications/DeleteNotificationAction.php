<?php
namespace Modules\Notification\Actions\Notifications;

use Modules\Notification\Repositories\NotificationRepository;
use Modules\Notification\Services\NotificationCacheService;

class DeleteNotificationAction
{
    public function __construct(protected NotificationRepository $repository, protected NotificationCacheService $service)
    {
    }

    public function execute(string $id): void
    {
        $notification = $this->repository->show($id);

        $this->service->forgetUnread($notification->user_id);
        $this->service->forgetFeed($notification->user_id);

        $notification->delete();
    }
}
