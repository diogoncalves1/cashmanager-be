<?php
namespace Modules\Notification\Listeners;

use Illuminate\Support\Facades\Notification;
use Modules\Notification\Events\NotificationCreated;
use Modules\Notification\Notifications\NotificationCreatedNotification;
use Modules\Notification\Services\NotificationCacheService;

class NotificationCreatedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(protected NotificationCacheService $service)
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(NotificationCreated $event)
    {
        $notification = $event->notification;

        $this->service->forgetFeed($notification->user_id);
        $this->service->forgetUnread($notification->user_id);

        $preferences = $notification->user
            ->notificationPreferences()
            ->where('type_code', $notification->type_code)
            ->first();

        if (! $preferences?->email) {
            return;
        }

        Notification::send($notification, new NotificationCreatedNotification($notification));
    }
}
