<?php

namespace Modules\SharedRoles\DataTables;

use Illuminate\Support\Facades\Auth;
use Modules\SharedRoles\Entities\SharedRole;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SharedRoleDataTable extends DataTable
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
        $canEdit = $user->can('authorization', 'editSharedRole');
        $canDestroy = $user->can('authorization', 'destroySharedRole');
        $canManage = $user->can('authorization', 'manageSharedRolePermission');

        return datatables()
            ->eloquent($query)
            ->editColumn('name', fn(SharedRole $sharedRole) =>  $sharedRole->name->{$user->preferences->lang} ?? $sharedRole->name->en)
            ->addColumn('action', function (SharedRole $sharedRole) use ($canEdit, $canDestroy, $canManage) {
                $btn = ' <div class="btn-group">';
                if ($canManage) {
                    $btn .= '<a title=\'Permissões\'
                data-toggle="tooltip" data-placement="top"
                class="btn btn-default mr-1"
                href="' . route("admin.shared-roles.manage", $sharedRole->id) . '">
                    <span class="m-l-5"><i class="fas fa-cogs"></i></span></a>';
                }
                if ($canEdit) {
                    $btn .= '<a title=\'Editar\'
                data-toggle="tooltip" data-placement="top"
                class="btn btn-default mr-1"
                href="' . route("admin.shared-roles.edit", $sharedRole->id) . '">
                    <span class="m-l-5"><i class="fa fa-pencil-alt"></i></span></a>';
                }
                if ($canDestroy) {
                    $btn .= '<a title=\'Remover\'
                data-toggle="tooltip" data-placement="top"
                class="btn btn-times btn-default mr-1"
                onclick="modalDelete(`' . route('admin.shared-roles.destroy', $sharedRole->id) . '`)">
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
     * @param SharedRole $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(SharedRole $model)
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
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(55)
                ->title('Actions'),
        ];
    }
}
