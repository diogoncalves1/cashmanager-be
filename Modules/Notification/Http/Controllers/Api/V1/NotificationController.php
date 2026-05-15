<?php
namespace Modules\Notification\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Http\Resources\DataTableResource;
use Illuminate\Support\Facades\Auth;
use Modules\Notification\Actions\Notifications\ArchiveNotificationAction;
use Modules\Notification\Actions\Notifications\DeleteNotificationAction;
use Modules\Notification\Actions\Notifications\MarkAllAsReadAction;
use Modules\Notification\Actions\Notifications\MarkAsReadAction;
use Modules\Notification\DataTables\NotificationDataTable;
use Modules\Notification\Http\Resources\NotificationCollection;
use Modules\Notification\Http\Resources\NotificationResource;
use Modules\Notification\Services\NotificationCacheService;

class NotificationController extends ApiController
{
    /**
     * Display a listing of the resource.
     * @param NotificationDataTable $dataTable
     */
    public function index(NotificationDataTable $dataTable)
    {
        return $this->safe(function () use ($dataTable) {
            $data = $dataTable->ajax()->getData(true);

            return new DataTableResource($data);
        });
    }

    /**
     * Display a listing of the resource.
     * @param NotificationCacheService $service
     */
    public function feed(NotificationCacheService $service)
    {
        return $this->safe(function () use ($service) {
            $notifications = $service->get(Auth::id());

            return $this->ok(new NotificationCollection($notifications), additionals: ['count' => $service->getUnreadCount(Auth::id())]);
        });
    }

    /**
     * Mark notification as archived
     * @param ArchiveNotificationAction $action
     * @param string $id
     */
    public function markAsArchived(ArchiveNotificationAction $action, string $id)
    {
        return $this->safe(function () use ($action, $id) {
            $action->execute($id);

            return $this->ok();
        });
    }

    /**
     * Mark all user notifications as read
     * @param MarkAllAsReadAction $action
     */
    public function markAllAsRead(MarkAllAsReadAction $action)
    {
        return $this->safe(function () use ($action) {
            $action->execute(Auth::user());

            return $this->ok();
        });
    }

    /**
     * Mark notification as read
     * @param MarkAsReadAction $action
     * @param string $id
     */
    public function markAsRead(MarkAsReadAction $action, string $id)
    {
        return $this->safe(function () use ($action, $id) {
            $notification = $action->execute($id);

            return $this->ok(new NotificationResource($notification));
        });
    }
    /**
     * Remove the specified resource from storage.
     * @param DeleteNotificationAction $action
     * @param string $id
     */
    public function destroy(DeleteNotificationAction $action, string $id)
    {
        return $this->safe(function () use ($action, $id) {
            $action->execute($id);

            return $this->ok();
        });
    }
}
