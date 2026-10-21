<?php
namespace Modules\Notification\Services;

use Illuminate\Support\Facades\DB;
use Modules\Notification\Actions\NotificationType\ProcessMailFieldsAction;
use Modules\Notification\Entities\BroadcastNotification;
use Modules\Notification\Repositories\BroadcastNotificationRepository;
use Modules\Notification\Repositories\NotificationTypeRepository;
use Modules\User\Entities\User;

class BroadcastNotificationService
{
    public function __construct(
        public BroadcastNotificationRepository $repo,
        public NotificationTypeRepository $typeRepo,
        public ProcessMailFieldsAction $processMailFields
    ) {}

    public function create(array $data, User $user): BroadcastNotification
    {
        return DB::transaction(function () use ($data, $user) {
            $typeData = array_merge(
                $data,
                ['is_broadcast' => true],
                $this->processMailFields->execute($data)
            );

            $type = $this->typeRepo->store($typeData);

            $notificationData                  = $data;
            $notificationData['type_code']     = $type->code;
            $notificationData['created_by_id'] = $user->id;

            return $this->repo->store($notificationData);
        });
    }

    public function update(array $data, string $id): BroadcastNotification
    {
        return DB::transaction(function () use ($data, $id) {
            $typeData = array_merge(
                $data,
                $this->processMailFields->execute($data)
            );

            $notification = $this->repo->show($id);

            $this->typeRepo->updateByCode($typeData, $notification->type_code);

            return $notification;
        });
    }

    public function destroy(string $id): BroadcastNotification
    {
        return DB::transaction(function () use ($id) {
            $notification = $this->repo->destroy($id);

            $this->typeRepo->destroyByCode($notification->type_code);

            return $notification;
        });
    }
}
