<?php
namespace Modules\Notification\Listeners;

use Illuminate\Support\Facades\Notification;
use Modules\Notification\Events\BroadcastNotificationCreated;
use Modules\Notification\Notifications\BroadcastNotificationCreatedNotification;

class BroadcastNotificationCreatedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(BroadcastNotificationCreated $event)
    {
        $notification = $event->notification;
        $user         = $event->user;
        Notification::send($notification, new BroadcastNotificationCreatedNotification($notification, $user));
    }
}
