<?php

namespace Modules\User\DataTables;

use Illuminate\Support\Facades\Auth;
use Modules\User\Entities\User;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UserDataTable extends DataTable
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
        $canEdit = $user->can('authorization', 'editUser');
        $canDestroy = $user->can('authorization', 'destroyUser');
        $canManage = $user->can('authorization', 'manageUserRoles');

        return datatables()
            ->eloquent($query)
            ->addColumn('roles', function (User $user) {
                return $user->roles()->pluck('name')->toArray();
            })
            ->addColumn('action', function (User $user) use ($canEdit, $canDestroy, $canManage) {
                $btn = ' <div class="btn-group">';
                if ($canManage) {
                    $btn .= '<a title=\'Papeis\'
                data-toggle="tooltip" data-placement="top"
                class="btn btn-default mr-1"
                href="' . route("admin.users.manage", $user->id) . '">
                    <span class="m-l-5"><i class="fas fa-cogs"></i></span></a>';
                }
                if ($canEdit) {
                    $btn .= '<a title=\'Editar\'
                data-toggle="tooltip" data-placement="top"
                class="btn btn-default mr-1"
                href="' . route("admin.users.edit", $user->id) . '">
                    <span class="m-l-5"><i class="fa fa-pencil-alt"></i></span></a>';
                }
                if ($canDestroy) {
                    $btn .= '<a title=\'Remover\'
                data-toggle="tooltip" data-placement="top"
                class="btn btn-times btn-default mr-1"
                onclick="modalDelete(`' . route('admin.users.destroy', $user->id) . '`)">
                    <span class="m-l-5"><i class="fa fa-trash"></i></span></a>';
                }

                $btn .= '</div>';

                return $btn;
            })
            ->filterColumn('roles', function ($query, $keyword) {
                $query->whereHas('roles', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param County $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(User $model)
    {
        $query = $model->newQuery();

        $query->whereDoesntHave('roles', function ($query) {
            $query->where('code', 'superAdmin');
        });

        return $query;
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
            Column::make('name')->title('Nome'),
            Column::make('email')->title('Email'),
            Column::make('roles')->title('Papeis'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(55)
                ->title('Actions'),
        ];
    }
}
