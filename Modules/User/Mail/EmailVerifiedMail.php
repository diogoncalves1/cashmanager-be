<?php
namespace Modules\User\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\EmailLayout\Entities\EmailLayout;
use Modules\User\Entities\User;

class EmailVerifiedMail extends Mailable
{
    use Queueable, SerializesModels;

    protected User $user;
    protected EmailLayout $emailLayout;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, EmailLayout $emailLayout)
    {
        $this->user        = $user;
        $this->emailLayout = $emailLayout;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        $user = $this->user;

        $emailSubject = str_replace(['[$user_name]'],
            [$this->user->name], $this->emailLayout->subject);
        $emailText = str_replace(['[$user_name]', '[$verification_link]'],
            [$this->user->name, config('app.frontend_url') . '/signin'], $this->emailLayout->email);

        $signature = str_replace('[$logo]', "<img style='width:200px'; margin-left: auto; margin-rigth:auto; margin-top: '10px'; margin-bottom: '10px'; src='" . config('app.url') . '/assets/images/logos/logo.png' . "'/>", $this->emailLayout->signature);

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
            ->markdown('user::emails.verified');

        return $mail;
    }
}
