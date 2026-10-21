<?php
namespace Modules\Notification\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Notification\Entities\Notification;

class NotificationCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    protected Notification $notification;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $notification     = $this->notification;
        $notificationType = $notification->notificationType;

        $keywords = $notificationType->keywords;

        $emailSubject = $notificationType->mail_subject;
        $emailText    = $notificationType->mail_message;
        $signature    = $notificationType->mail_signature;
        $user         = $notification->user;

        foreach ($keywords as $keyword) {
            $emailSubject = str_replace($keyword, $this->notification->data[$keyword], $emailSubject);

            $emailText = str_replace($keyword, $this->notification->data[$keyword], $emailText);

            $signature = str_replace($keyword, $this->notification->data[$keyword], $signature);
        }

        $mail = $this->from(config('mail.from.address'), config('app.name'))
            ->subject($emailSubject);

        if (! empty($user)) {
            $mail->to($user->email);
        }

        $mail->with([
            'text'      => $emailText,
            'email'     => config('mail.from.address'),
            'signature' => $signature,
        ])
            ->markdown('notification::emails.mail');

        return $mail;
    }
}
