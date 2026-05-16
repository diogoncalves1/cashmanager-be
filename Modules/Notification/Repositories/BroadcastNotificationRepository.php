<?php
namespace Modules\Notification\Repositories;

use App\Repositories\RepositorySolidInterface;
use Illuminate\Support\Collection;
use Modules\Notification\Entities\BroadcastNotification;

class BroadcastNotificationRepository implements RepositorySolidInterface
{
    public function all(): Collection
    {
        return BroadcastNotification::all();
    }

    public function store(array $data): BroadcastNotification
    {
        return BroadcastNotification::create($data);
    }

    public function update(array $data, string $id): BroadcastNotification
    {
        $notification = $this->show($id);

        $notification->update($data);

        return $notification;
    }

    public function show(string $id): BroadcastNotification
    {
        return BroadcastNotification::findOrFail($id);
    }

    public function destroy(string $id): BroadcastNotification
    {
        $notification = $this->show($id);

        $notification->delete();

        return $notification;
    }
}
