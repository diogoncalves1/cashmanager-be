<?php
namespace Modules\Notification\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Modules\Notification\Entities\Notification as EntitiesNotification;
use Modules\Notification\Mail\NotificationCreatedMail;

class NotificationCreatedNotification extends Notification
{
    use Queueable;

    private EntitiesNotification $notification;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(EntitiesNotification $notification)
    {
        $this->notification = $notification;
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
        return (new NotificationCreatedMail($this->notification));
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
