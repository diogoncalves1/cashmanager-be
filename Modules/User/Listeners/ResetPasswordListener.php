<?php
namespace Modules\User\Listeners;

use Illuminate\Support\Facades\Notification;
use Modules\User\Events\ResetPassword;
use Modules\User\Notifications\ResetPasswordNotification;

class ResetPasswordListener
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
     * @param object $event
     * @return void
     */
    public function handle(ResetPassword $event)
    {
        $user = $event->user;
        Notification::send($user, new ResetPasswordNotification($user, $event));

    }
}
