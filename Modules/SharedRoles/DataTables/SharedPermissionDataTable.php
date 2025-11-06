<?php

namespace Modules\SharedRoles\DataTables;

use Illuminate\Support\Facades\Auth;
use Modules\Permission\Entities\Permission;
use Modules\SharedRoles\Entities\SharedPermission;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SharedPermissionDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return DataTableAbstract
     */
    public function dataTable($query)
    {
        $user = Auth::user();
        $canEdit = $user->can('authorization', 'editSharedPermission');
        $canDestroy = $user->can('authorization', 'destroySharedPermission');

        return datatables()
            ->eloquent($query)
            ->addColumn('action', function (SharedPermission $permission) use ($canEdit, $canDestroy) {
                $btn = ' <div class="btn-group">';
                if ($canEdit) {
                    $btn .= '<a title=\'Editar\'
                data-toggle="tooltip" data-placement="top"
                class="btn btn-default mr-1"
                href="' . route("admin.shared-permissions.edit", $permission->id) . '">
                    <span class="m-l-5"><i class="fa fa-pencil-alt"></i></span></a>';
                }
                if ($canDestroy) {
                    $btn .= '<a title=\'Remover\'
                data-toggle="tooltip" data-placement="top"
                class="btn btn-times btn-default mr-1"
                onclick="modalDelete(`' . route('admin.shared-permissions.destroy', $permission->id) . '`)">
                    <span class="m-l-5"><i class="fa fa-trash"></i></span></a>';
                }

                $btn .= '</div>';

                return $btn;
            })
            ->rawColumns(['action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param SharedPermission $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(SharedPermission $model)
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
            Column::make('code')->title('Código'),
            Column::make('name')->title('Nome'),
            Column::make('category')->title('Category'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(55)
                ->title('Actions'),
        ];
    }
}
