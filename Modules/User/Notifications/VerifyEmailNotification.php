<?php
namespace Modules\User\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Modules\EmailLayout\Repositories\EmailLayoutRepository;
use Modules\User\Entities\User;
use Modules\User\Mail\VerifyEmailMail;

class VerifyEmailNotification extends Notification
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
        $this->emailLayout     = $emailLayoutRepository->showByEmailTypeCode('verify_email');

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
        return (new VerifyEmailMail($this->user, $this->emailLayout));
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
