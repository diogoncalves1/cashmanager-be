<?php
namespace Modules\Notification\Interfaces;

use Illuminate\Database\Eloquent\Builder;

interface NotificationRepositoryInterface
{
    public function query(): Builder;
    public function store(array $data);
    public function show(string $id);
}
