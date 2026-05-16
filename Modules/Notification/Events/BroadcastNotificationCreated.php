<?php
namespace Modules\Notification\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Notification\Entities\BroadcastNotification;
use Modules\Notification\Entities\Notification;
use Modules\User\Entities\User;

class BroadcastNotificationCreated
{
    use SerializesModels;

    public $user;
    public $notification;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(BroadcastNotification $notification, User $user)
    {
        $this->notification = $notification;
        $this->user         = $user;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
