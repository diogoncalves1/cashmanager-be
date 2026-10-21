<?php
namespace Modules\Notification\Actions\Notifications;

use Modules\Notification\Entities\Notification;
use Modules\Notification\Repositories\NotificationRepository;
use Modules\Notification\Services\NotificationCacheService;

class ArchiveNotificationAction
{
    public function __construct(protected NotificationRepository $repository, protected NotificationCacheService $service)
    {
    }

    public function execute(string $id): Notification
    {
        $notification = $this->repository->show($id);

        if ($notification->archived_at) {
            return $notification;
        }

        $this->service->forgetUnread($notification->user_id);
        $this->service->forgetFeed($notification->user_id);

        $notification->update([
            'archived_at' => now(),
        ]);

        return $notification;
    }
}
