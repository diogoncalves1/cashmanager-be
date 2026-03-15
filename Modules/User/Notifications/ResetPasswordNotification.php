<?php
namespace Modules\User\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Modules\EmailLayout\Repositories\EmailLayoutRepository;
use Modules\User\Entities\User;
use Modules\User\Mail\ResetPasswordMail;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    private User $user;
    private $emailLayout;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user            = $user;
        $emailLayoutRepository = new EmailLayoutRepository();
        $this->emailLayout     = $emailLayoutRepository->showByEmailTypeCode('reset_password');

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
     * @return ResetPasswordMail
     */
    public function toMail($notifiable): ResetPasswordMail
    {

        return (new ResetPasswordMail($this->user, $this->emailLayout));
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
