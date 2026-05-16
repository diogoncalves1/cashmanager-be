<?php
namespace Modules\Notification\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Notification\Entities\BroadcastNotification;
use Modules\Notification\Events\BroadcastNotificationCreated;
use Modules\User\Entities\User;

class SendNotificationEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public User $user;
    public BroadcastNotification $notification;

    /**
     * Create a new job instance.
     */
    public function __construct($user, $notification)
    {
        $this->user         = $user;
        $this->notification = $notification;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        event(new BroadcastNotificationCreated($this->notification, $this->user));
    }
}
