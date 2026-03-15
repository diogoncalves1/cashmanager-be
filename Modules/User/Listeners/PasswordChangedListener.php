<?php
namespace Modules\User\Listeners;

use Illuminate\Support\Facades\Notification;
use Modules\User\Events\PasswordChanged;
use Modules\User\Notifications\PasswordChangedNotification;

class PasswordChangedListener
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
    public function handle(PasswordChanged $event)
    {
        $user = $event->user;
        Notification::send($user, new PasswordChangedNotification($user));
    }
}
