<?php
namespace Modules\Notification\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Notification\Entities\NotificationType;
use Modules\User\Entities\User;

class NotificationTypeMail extends Mailable
{
    use Queueable, SerializesModels;

    protected NotificationType $type;
    protected User $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(NotificationType $type, User $user)
    {
        $this->type = $type;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $notificationType = $this->type;
        $user             = $this->user;

        $emailSubject = $notificationType->mail_subject[$user->preferences->lang];
        $emailText    = $notificationType->mail_message[$user->preferences->lang];
        $signature    = $notificationType->mail_signature[$user->preferences->lang];

        $mail = $this->from(config('mail.from.address'), config('app.name'))
            ->subject($emailSubject);

        $mail->with([
            'text'      => $emailText,
            'email'     => config('mail.from.address'),
            'signature' => $signature,
        ])
            ->markdown('notification::emails.mail');

        return $mail;
    }

    public function preview()
    {
        return $this->render();
    }
}
