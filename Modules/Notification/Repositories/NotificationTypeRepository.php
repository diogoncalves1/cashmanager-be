<?php
namespace Modules\Notification\Repositories;

use App\Repositories\FilesRepository;
use App\Repositories\RepositorySolidInterface;
use Illuminate\Support\Collection;
use Modules\Notification\Entities\NotificationType;

class NotificationTypeRepository extends FilesRepository implements RepositorySolidInterface
{
    public function all(): Collection
    {
        return NotificationType::all();
    }

    public function store(array $data): NotificationType
    {
        return NotificationType::create($data);
    }

    public function update(array $data, string $id): NotificationType
    {
        $type = $this->show($id);

        $type->update($data);

        return $type;
    }

    public function updateByCode(array $data, string $code): NotificationType
    {
        $type = $this->showByCode($code);

        $type->update($data);

        return $type;
    }

    public function show(string $id): NotificationType
    {
        return NotificationType::findOrFail($id);
    }

    public function showByCode(string $code): NotificationType
    {
        return NotificationType::where('code', $code)->firstOrFail();
    }

    public function destroy(string $id): NotificationType
    {
        $type = $this->show($id);

        $type->delete();

        return $type;
    }

    public function destroyByCode(string $code): NotificationType
    {
        $type = $this->showByCode($code);

        $type->delete();

        return $type;
    }

    public function checkIfCodeExists(string $code, string $id): bool
    {
        return NotificationType::where('code', $code)
            ->where('id', '!=', $id)
            ->exists();
    }
}
