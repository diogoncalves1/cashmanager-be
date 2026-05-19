<?php
namespace Modules\Notification\Observers;

use Modules\Notification\Entities\BroadcastNotification;
use Modules\Notification\Jobs\SendNotificationEmailJob;
use Modules\User\Entities\User;

class BroadcastNotificationObserver
{
    /**
     * Handle the BroadcastNotificationObserver "created" event.
     */
    public function created(BroadcastNotification $broadcastNotification): void
    {
        if ($broadcastNotification->send_email) {
            $delay     = 10;
            $chunkSize = 50;

            User::whereDoesntHave('roles', function ($query) {
                $query->whereIn('code', ['admin', 'superAdmin']);
            })
                ->chunkById($chunkSize, function ($users) use ($broadcastNotification, &$delay) {
                    foreach ($users as $user) {
                        SendNotificationEmailJob::dispatch($user, $broadcastNotification)
                            ->onQueue('emails')
                            ->delay(now()->addSeconds($delay));

                        $delay += 10;
                    }
                });
        }
    }

    /**
     * Handle the BroadcastNotificationObserver "updated" event.
     */
    public function updated(BroadcastNotification $broadcastNotification): void
    {}

    /**
     * Handle the BroadcastNotificationObserver "deleted" event.
     */
    public function deleted(BroadcastNotification $broadcastNotification): void
    {}

    /**
     * Handle the BroadcastNotificationObserver "restored" event.
     */
    public function restored(BroadcastNotification $broadcastNotification): void
    {}

    /**
     * Handle the BroadcastNotificationObserver "force deleted" event.
     */
    public function forceDeleted(BroadcastNotification $broadcastNotification): void
    {}
}
