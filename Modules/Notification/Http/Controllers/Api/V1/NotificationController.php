<?php

namespace Modules\Notification\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Notification\Actions\Notifications\ArchiveNotificationAction;
use Modules\Notification\Actions\Notifications\DeleteNotificationAction;
use Modules\Notification\Actions\Notifications\MarkAllAsReadAction;
use Modules\Notification\Actions\Notifications\MarkAsReadAction;
use Modules\Notification\Http\Resources\NotificationCollection;
use Modules\Notification\Http\Resources\NotificationResource;
use Modules\Notification\Paginates\NotificationPaginate;
use Modules\Notification\Services\NotificationCacheService;

class NotificationController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @param  NotificationPaginate  $dataTable
     */
    public function index(Request $request, NotificationPaginate $paginate, NotificationCacheService $service): JsonResponse
    {
        return $this->safe(function () use ($request, $paginate, $service) {
            return $this->respondModulePaginated($paginate, $request, ['count' => $service->getUnreadCount($request->user()->id)]);
        });
    }

    /**
     * Display a listing of the resource.
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
     */
    public function destroy(DeleteNotificationAction $action, string $id)
    {
        return $this->safe(function () use ($action, $id) {
            $action->execute($id);

            return $this->ok();
        });
    }
}
