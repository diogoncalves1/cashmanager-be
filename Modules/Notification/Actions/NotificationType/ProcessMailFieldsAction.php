<?php
namespace Modules\Notification\Actions\NotificationType;

use App\Repositories\FilesRepository;

class ProcessMailFieldsAction extends FilesRepository
{

    /**
     * Processes the email data and returns the formatted result.
     *
     * @param array $data Associative array containing:
     *                    - 'mail_subject'   => array of email subjects
     *                    - 'mail_message'   => array of email messages
     *                    - 'mail_signature' => array of email signatures
     *
     * @return array
     */
    public function execute(array $data): array
    {
        if (! isset($data['send_email']) || isset($data['send_email']) && ! $data['send_email']) {
            return [];
        }

        $newData                 = [];
        $newData['mail_subject'] = $data['mail_subject'];

        foreach ($data['mail_message'] as $lang => $mailMessage) {
            $newData['mail_message'][$lang] = $this->processMailHtml($mailMessage);
        }
        foreach ($data['mail_signature'] as $lang => $mailSignature) {
            $newData['mail_signature'][$lang] = $this->processMailHtml($mailSignature);
        }

        return $newData;
    }

    private function processMailHtml(string $htmlData): string
    {
        return trim($this->uploadImagesFromHtml($htmlData, 'upload/broadcast-notifications', 'broadcast-notification'));
    }
}
