<?php

namespace Modules\Category\DataTables;

use Illuminate\Support\Facades\Auth;
use Modules\Category\Entities\Category;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CategoryDataTable extends DataTable
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

        $canEditDefault = $user->can('authorization', 'editCategoryDefault');
        $canDestroyDefault = $user->can('authorization', 'destroyCategoryDefault');

        return datatables()
            ->eloquent($query)
            ->editColumn('name', function (Category $category) {
                return $category->name->en;
            })
            ->editColumn('type', function (Category $category) {
                return __('category::attributes.categories.type.' . $category->type);
            })
            ->editColumn('icon', function (Category $category) {
                return "<p style='color: $category->color'><i class='nav-icon fas $category->icon'></i></p>";
            })
            ->addColumn('parent', function (Category $category) {
                return $category->parent?->name->{app()->getLocale()};
            })
            ->addColumn('action', function (Category $category) use ($canEditDefault, $canDestroyDefault) {
                $btn = ' <div class="btn-group">';
                if ($canEditDefault) {
                    $btn .= '<a title=\'Editar\'
                data-toggle="tooltip" data-placement="top"
                class="btn btn-default mr-1"
                href="' . route("admin.categories.edit", $category->id) . '">
                    <span class="m-l-5"><i class="fa fa-pencil-alt"></i></span></a>';
                }
                if ($canDestroyDefault) {
                    $btn .= '<a title=\'Remover\'
                data-toggle="tooltip" data-placement="top"
                class="btn btn-times btn-default mr-1"
                onclick="modalDelete(`' . route('admin.categories.destroy', $category->id) . '`)">
                    <span class="m-l-5"><i class="fa fa-trash"></i></span></a>';
                }

                $btn .= '</div>';

                return $btn;
            })
            ->rawColumns(['action', 'icon']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param Category $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Category $model)
    {
        $query = $model->newQuery();

        $query->distinct()->where('default', 1);

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
            Column::make('name')->title('Name'),
            Column::make('type')->title('Tipo'),
            Column::make('icon')->title('Icone'),
            Column::make('parent')->title('Parente'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(55)
                ->title('Ações'),
        ];
    }
}
