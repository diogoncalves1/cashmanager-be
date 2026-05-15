<?php
namespace Modules\Notification\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Modules\Notification\Entities\Notification;
use Modules\Notification\Interfaces\NotificationRepositoryInterface;

class NotificationRepository implements NotificationRepositoryInterface
{
    public function store(array $data)
    {
        return Notification::create($data);
    }

    public function show(string $id)
    {
        return Notification::findOrFail($id);
    }

    public function query(): Builder
    {
        return Notification::query();
    }
}
