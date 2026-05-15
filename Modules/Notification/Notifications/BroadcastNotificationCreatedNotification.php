<?php
namespace Modules\Notification\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Modules\Notification\Entities\BroadcastNotification;
use Modules\Notification\Mail\BroadcastNotificationCreatedMail;
use Modules\User\Entities\User;

class BroadcastNotificationCreatedNotification extends Notification
{
    use Queueable;

    private BroadcastNotification $notification;
    private User $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(BroadcastNotification $notification, User $user)
    {
        $this->notification = $notification;
        $this->user         = $user;

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new BroadcastNotificationCreatedMail($this->notification, $this->user));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
