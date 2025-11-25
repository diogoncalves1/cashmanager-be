<?php
namespace Modules\Language\DataTables;

use Modules\Language\Entities\Language;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class LanguageDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('action', function (Language $language) {
                return ' <div class="btn-group">
                 <a title=\'Editar\'
                data-toggle="tooltip" data-placement="top"
                class="btn btn-default mr-1"
                href="' . route("admin.languages.edit", $language->id) . '">
                    <span class="m-l-5"><i class="fa fa-pencil-alt"></i></span></a>
                    <a title=\'Remover\'
                data-toggle="tooltip" data-placement="top"
                class="btn btn-times btn-default mr-1"
                onclick="modalDelete(`' . route('admin.languages.destroy', $language->id) . '`)">
                    <span class="m-l-5"><i class="fa fa-trash"></i></span></a>
                    </div>';
            })
            ->rawColumns(['action']);
    }

/**
 * Get query source of dataTable.
 *
 * @param Language $model
 * @return \Illuminate\Database\Eloquent\Builder
 */
    public function query(Language $model)
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
