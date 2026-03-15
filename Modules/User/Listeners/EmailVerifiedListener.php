<?php
namespace Modules\User\Listeners;

use Illuminate\Support\Facades\Notification;
use Modules\User\Events\EmailVerified;
use Modules\User\Notifications\EmailVerifiedNotification;

class EmailVerifiedListener
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
    public function handle(EmailVerified $event)
    {
        $user = $event->user;
        Notification::send($user, new EmailVerifiedNotification($user));
    }
}
