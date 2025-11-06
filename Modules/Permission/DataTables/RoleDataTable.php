<?php

namespace Modules\Permission\DataTables;

use Illuminate\Support\Facades\Auth;
use Modules\Permission\Entities\Role;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class RoleDataTable extends DataTable
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
        $canManage = $user->can('authorization', 'manageRole');
        $canEdit = $user->can('authorization', 'editRole');
        $canDestroy = $user->can('authorization', 'destroyRole');

        return datatables()
            ->eloquent($query)
            ->addColumn('action', function (Role $role) use ($canEdit, $canDestroy, $canManage) {
                $btn = '<div class="btn-group">';
                if ($canManage) {
                    $btn .= '<a title=\'Permissões\'
                data-toggle="tooltip" data-placement="top"
                class="btn btn-default mr-1"
                href="' . route('admin.roles.manage', $role->id) . '">
                    <span class="m-l-5"><i class="fas fa-cogs"></i></span></a>';
                }
                if ($canEdit) {
                    $btn .= '<a title=\'Editar\'
                data-toggle="tooltip" data-placement="top"
                class="btn btn-default mr-1"
                href="' . route('admin.roles.edit', $role->id) . '">
                    <span class="m-l-5"><i class="fa fa-pencil-alt"></i></span></a>';
                }
                if ($canDestroy) {
                    $btn .= '<a title=\'Remover\'
                data-toggle="tooltip" data-placement="top"
                class="btn btn-times btn-default mr-1"
                onclick="modalDelete(`' . route('admin.roles.destroy', $role->id) . '`)">
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
     * @param Role $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Role $model)
    {
        return $model->newQuery()->where('code', '!=', 'superAdmin');
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
