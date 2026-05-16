<?php
namespace Modules\Notification\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Notification\Actions\NotificationType\RenderNotificationTemplateAction;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();

        $data = app(RenderNotificationTemplateAction::class)->execute($this->notificationType, $this->data, $this->notificationType->keywords, $user);

        return [
            'id'        => $this->id ?? null,
            'title'     => $data['title'],
            'message'   => $data['message'],
            'pathname'  => $data['pathname'],
            'createdAt' => $this->created_at ?? null,
            'readAt'    => $this->read_at ?? null,
        ];
    }
}
