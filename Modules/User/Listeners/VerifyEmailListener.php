<?php
namespace Modules\User\Listeners;

use Illuminate\Support\Facades\Notification;
use Modules\User\Events\VerifyEmail;
use Modules\User\Notifications\VerifyEmailNotification;

class VerifyEmailListener
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
    public function handle(VerifyEmail $event)
    {
        $user = $event->user;
        Notification::send($user, new VerifyEmailNotification($user));
    }
}
