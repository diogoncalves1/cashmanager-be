<?php
namespace Modules\Notification\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Notification\Actions\NotificationType\RenderNotificationTemplateAction;
use Modules\Notification\Entities\BroadcastNotification;
use Modules\User\Entities\User;

class BroadcastNotificationCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    protected BroadcastNotification $notification;
    protected User $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(BroadcastNotification $notification, User $user)
    {
        $this->notification = $notification;
        $this->user         = $user;
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

        $user = $this->user;

        $data = app(RenderNotificationTemplateAction::class)->execute($notificationType, $notification->data, $keywords, $user);

        $mail = $this->from(config('mail.from.address'), config('app.name'))
            ->subject($data['mailSubject']);

        if (! empty($user)) {
            $mail->to($user->email);
        }

        $mail->with([
            'text'      => $data['mailText'],
            'email'     => config('mail.from.address'),
            'signature' => $data['mailSignature'],
        ])
            ->markdown('notification::emails.mail');

        return $mail;
    }

    public function preview()
    {
        return $this->render();
    }
}
