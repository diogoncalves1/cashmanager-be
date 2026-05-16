<?php
namespace Modules\Notification\Actions\Notifications;

use Modules\Notification\Entities\Notification;
use Modules\Notification\Repositories\NotificationRepository;
use Modules\Notification\Services\NotificationCacheService;

class MarkAsReadAction
{
    public function __construct(protected NotificationRepository $repository, protected NotificationCacheService $service)
    {
    }

    public function execute(string $id): Notification
    {
        $notification = $this->repository->show($id);

        if ($notification->read_at) {
            return $notification;
        }

        $this->service->forgetUnread($notification->user_id);

        $notification->update([
            'read_at' => now(),
        ]);

        return $notification;
    }
}
