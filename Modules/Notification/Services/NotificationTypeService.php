<?php
namespace Modules\Notification\Services;

use Illuminate\Support\Facades\DB;
use Modules\Notification\Actions\NotificationType\ProcessMailFieldsAction;
use Modules\Notification\Entities\NotificationType;
use Modules\Notification\Repositories\NotificationTypeRepository;

class NotificationTypeService
{
    public function __construct(
        public NotificationTypeRepository $repo,
        public ProcessMailFieldsAction $processMailFields
    ) {}

    public function create(array $data): NotificationType
    {
        return DB::transaction(function () use ($data) {
            $typeData = array_merge(
                $data,
                $this->processMailFields->execute($data)
            );

            $type = $this->repo->store($typeData);

            $type->keywords()->sync($data['keywords']);

            return $type;
        });
    }

    public function update(array $data, string $id): NotificationType
    {
        return DB::transaction(function () use ($data, $id) {
            $typeData = array_merge(
                $data,
                $this->processMailFields->execute($data)
            );

            $type = $this->repo->update($typeData, $id);

            $type->keywords()->sync($data['keywords'] ?? []);

            return $type;
        });
    }

    public function destroy(string $id): NotificationType
    {
        return DB::transaction(function () use ($id) {
            $type = $this->repo->destroy($id);

            return $type;
        });
    }
}
