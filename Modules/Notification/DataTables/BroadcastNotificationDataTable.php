<?php
namespace Modules\Notification\DataTables;

use Illuminate\Support\Facades\Auth;
use Modules\Notification\Entities\BroadcastNotification;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BroadcastNotificationDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return DataTableAbstract
     */
    public function dataTable($query)
    {
        $user       = Auth::user();
        $canView    = $user->can('authorization', 'viewNotificationMail');
        $canEdit    = $user->can('authorization', 'editNotifications');
        $canDestroy = $user->can('authorization', 'destroyNotifications');

        return datatables()
            ->eloquent($query)
            ->addColumn('title', function (BroadcastNotification $notification) {
                return $notification->notificationType->title['pt'] ?? '';
            })
            ->addColumn('message', function (BroadcastNotification $notification) {
                return $notification->notificationType->message['pt'] ?? '';
            })
            ->addColumn('created_by', function (BroadcastNotification $notification) {
                return $notification->creator?->name;
            })
            ->editColumn('send_email', function (BroadcastNotification $notification) {
                return $notification->send_email ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>';
            })
            ->addColumn('action', function (BroadcastNotification $notification) use ($canView, $canEdit, $canDestroy) {
                $btn = ' <div class="btn-group">';
                if ($canView && $notification->send_email) {
                    $btn .= '<a title=\'Vizualizar Email\'
                data-toggle="tooltip" data-placement="top" target="_blank"
                class="btn btn-default mr-1"
                href="' . route("admin.notifications.show", $notification->id) . '">
                    <span class="m-l-5"><i class="fa fa-eye"></i></span></a>';
                }
                if ($canEdit) {
                    $btn .= '<a title=\'Editar\'
                data-toggle="tooltip" data-placement="top"
                class="btn btn-default mr-1"
                href="' . route("admin.notifications.edit", $notification->id) . '">
                    <span class="m-l-5"><i class="fa fa-pencil-alt"></i></span></a>';
                }
                if ($canDestroy) {
                    $btn .= '<a title=\'Remover\'
                data-toggle="tooltip" data-placement="top"
                class="btn btn-times btn-default mr-1"
                onclick="modalDelete(`' . route('admin.notifications.destroy', $notification->id) . '`)">
                    <span class="m-l-5"><i class="fa fa-trash"></i></span></a>';
                }

                $btn .= '</div>';

                return $btn;
            })
            ->filterColumn('created_by', function ($query, $keyword) {
                $query->whereHas('creator', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['action', 'send_email']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param BroadcastNotification $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(BroadcastNotification $model)
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('data-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->postAjax()
            ->language('/vendor/datatables-portuguese.json')
            ->orderBy(1, 'asc')
            ->dom('Bfrtip')
            ->drawCallback(" function () {
                    $('[data-toggle=\"tooltip\"]').tooltip();
                }
                 ");
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            Column::make('title')->title('Título'),
            Column::make('message')->title('Mensagem'),
            Column::make('created_by')->title('Criada por'),
            Column::computed('send_email')
                ->exportable(false)
                ->printable(false)
                ->width(100)
                ->title('Email enviado'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(55)
                ->title('Ações'),
        ];
    }
}
