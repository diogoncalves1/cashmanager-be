<?php
namespace Modules\Notification\DataTables;

use Illuminate\Support\Facades\Auth;
use Modules\Notification\Actions\NotificationType\RenderNotificationTemplateAction;
use Modules\Notification\Entities\BroadcastNotification;
use Modules\Notification\Entities\Notification;
use Yajra\DataTables\Services\DataTable;

class NotificationDataTable extends DataTable
{
    public function __construct(protected RenderNotificationTemplateAction $action)
    {
    }

    public function dataTable($query)
    {
        $user = Auth::user();

        return datatables()
            ->eloquent($query)
            ->addColumn('pathname', function (Notification $notification) use ($user) {
                $notificationType = $notification->notificationType;

                $data = $this->action->execute($notificationType, $notification->data, $notificationType->keywords, $user);

                return $data['pathname'];
            })
            ->addColumn('title', function (Notification $notification) use ($user) {
                $notificationType = $notification->notificationType;

                $data = $this->action->execute($notificationType, $notification->data, $notificationType->keywords, $user);

                return $data['title'];
            })
            ->addColumn('message', function (Notification $notification) use ($user) {
                $notificationType = $notification->notificationType;

                $data = $this->action->execute($notificationType, $notification->data, $notificationType->keywords, $user);

                return $data['message'];
            })
            ->addColumn('createdAt', fn(Notification $notification) => $notification->created_at)
            ->addColumn('readAt', fn(Notification $notification) => $notification->read_at)
            ->removeColumn(['user_id', 'data', 'type_code', 'created_at', 'read_at', 'updated_at', 'id'])
            ->rawColumns(['message', 'title']);

    }

    public function query(Notification $model)
    {
        $userNotifications = $model->newQuery()
            ->where('user_id', Auth::id())
            ->whereNull('archived_at');

        $broadcastNotifications = BroadcastNotification::query();

        return $userNotifications
            ->unionAll($broadcastNotifications)
            ->orderByDesc('created_at');
    }

}
