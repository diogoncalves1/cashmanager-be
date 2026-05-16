<?php
namespace Modules\Notification\Actions\NotificationType;

use Illuminate\Support\Collection;
use Modules\Notification\Entities\NotificationKeyword;
use Modules\Notification\Entities\NotificationType;
use Modules\User\Entities\User;

class RenderNotificationTemplateAction
{

    public function execute(NotificationType $notificationType, array $notificationData, Collection $keywords, User $user): array
    {
        $userLang = $user->preferences->lang;

        $data = [
            'title'         => $notificationType->title[$userLang],
            'message'       => $notificationType->message[$userLang],
            'mailSubject'   => $notificationType->mail_subject[$userLang],
            'mailMessage'   => $notificationType->mail_message[$userLang],
            'mailSignature' => $notificationType->mail_signature[$userLang],
            'pathname'      => $notificationType->pathname,
        ];

        foreach ($keywords as $keyword) {
            $data['title']         = $this->replace($keyword, $notificationData, $data['title']);
            $data['message']       = $this->replace($keyword, $notificationData, $data['message']);
            $data['mailSubject']   = $this->replace($keyword, $notificationData, $data['mailSubject']);
            $data['mailMessage']   = $this->replace($keyword, $notificationData, $data['mailMessage']);
            $data['mailSignature'] = $this->replace($keyword, $notificationData, $data['mailSignature']);
            $data['pathname']      = $this->replace($keyword, $notificationData, $data['pathname']);
        }

        return $data;
    }

    private function replace(NotificationKeyword $keyword, array $notificationData, string $string): string
    {
        return trim(str_replace($keyword->keyword, $notificationData[$this->replaceKeyword($keyword->keyword)], $string));
    }

    private function replaceKeyword(string $keyword): string
    {
        $key = str_replace('[$', '', $keyword);
        $key = str_replace(']', '', $key);

        return trim($key);
    }
}
