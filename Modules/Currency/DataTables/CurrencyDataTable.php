<?php

namespace Modules\Currency\DataTables;

use Illuminate\Support\Facades\Auth;
use Modules\Currency\Entities\Currency;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CurrencyDataTable extends DataTable
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
        $canEdit = $user->can('authorization', 'editCurrency');
        $canDestroy = $user->can('authorization', 'destroyCurrency');

        return datatables()
            ->eloquent($query)
            ->editColumn('name', function (Currency $currency) {
                return $currency->name->en;
            })
            ->addColumn('action', function (Currency $currency) use ($canEdit, $canDestroy) {
                $btn = ' <div class="btn-group">';
                if ($canEdit) {
                    $btn .= '<a title=\'Editar\'
                data-toggle="tooltip" data-placement="top"
                class="btn btn-default mr-1"
                href="' . route("admin.currencies.edit", $currency->id) . '">
                    <span class="m-l-5"><i class="fa fa-pencil-alt"></i></span></a>';
                }
                if ($canDestroy) {
                    $btn .= '<a title=\'Remover\'
                data-toggle="tooltip" data-placement="top"
                class="btn btn-times btn-default mr-1"
                onclick="modalDelete(`' . route('api.currencies.destroy', $currency->id) . '`)">
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
     * @param Currency $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Currency $model)
    {
        $query = $model->newQuery();

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
            Column::make('code')->title('Código'),
            Column::make('symbol')->title('Símbolo'),
            Column::make('rate')->title('Taxas (Realação USD)'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(55)
                ->title('Ações'),
        ];
    }
}
