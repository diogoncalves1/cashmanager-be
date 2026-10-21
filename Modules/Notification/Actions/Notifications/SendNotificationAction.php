<?php
namespace Modules\Notification\Actions\Notifications;

use Modules\Notification\Data\Notifications\CreateNotificationDto;
use Modules\Notification\Entities\Notification;
use Modules\Notification\Events\NotificationCreated;
use Modules\Notification\Repositories\NotificationRepository;
use Modules\User\Entities\User;

class SendNotificationAction
{
    public function __construct(protected NotificationRepository $repository)
    {
    }

    public function execute(CreateNotificationDto $dto, User $user): Notification
    {
        $notification = $this->repository->store([
            'data'      => $dto->data,
            'type_code' => $dto->type_code,
            'user_id'   => $user->id,
        ]);

        event(new NotificationCreated($notification));

        return $notification;
    }

}
