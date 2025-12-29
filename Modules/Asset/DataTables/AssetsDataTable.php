<?php
namespace Modules\Asset\DataTables;

use Illuminate\Support\Facades\Auth;
use Modules\Asset\Entities\Asset;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class AssetsDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $user = Auth::user();

        $canEdit    = $user->can('authorization', 'editAsset');
        $canDestroy = $user->can('authorization', 'destroyAsset');

        return datatables()
            ->eloquent($query)
            ->addColumn('action', function (Asset $asset) use ($canEdit, $canDestroy) {
                $btn = ' <div class="btn-group">';
                if ($canEdit) {
                    $btn .= '<a title=\'Editar\'
            data-toggle="tooltip" data-placement="top"
            class="btn btn-default mr-1"
            href="' . route("admin.assets.edit", [$asset->id]) . '">
                <span class="m-l-5"><i class="fa fa-pencil-alt"></i></span></a>';
                }
                if ($canDestroy) {
                    $btn .= '<a title=\'Remover\'
                data-toggle="tooltip" data-placement="top"
                class="btn btn-times btn-default mr-1"
                onclick="modalDelete(`' . route('admin.assets.destroy', [$asset->id]) . '`)">
                    <span class="m-l-5"><i class="fa fa-trash"></i></span></a>';
                }

                $btn .= '</div>';
                return $btn;
            })->rawColumns(['action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param Asset $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Asset $model)
    {
        $query = $model->newQuery();

        if (! is_null($this->dividends)) {
            $query->where('has_dividends', $this->dividends);
        }
        if (! is_null($this->marketCap)) {
            $query->whereRaw("JSON_EXTRACT(fundamentals_json, '$.marketCap')" . $this->marketCapOperator . "" . $this->marketCap);
        }
        if (! is_null($this->exchange)) {
            $query->where("exchange", $this->exchange);
        }
        if (! is_null($this->dividendYield)) {
            $query->whereRaw("JSON_EXTRACT(dividends_json, '$.dividendYield')" . $this->dividendYieldOperator . "" . $this->dividendYield);
        }
        if ($this->checkDivYield) {
            $query->whereRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(dividends_json, '$.dividendYield')) as DECIMAL)IS null");
        }
        if ($this->checkExDivDate) {
            $query->whereRaw("STR_TO_DATE(
                                JSON_UNQUOTE(JSON_EXTRACT(dividends_json, '$.exDividendDate')),
                                '%Y-%m-%d'
                            ) IS NULL");
        }
        if ($this->checkDivDate) {
            $query->whereRaw("STR_TO_DATE(
                                JSON_UNQUOTE(JSON_EXTRACT(dividends_json, '$.dividendDate')),
                                '%Y-%m-%d'
                            ) IS NULL");
        }

        return $query;
    }

/**
 * Optional method if you want to use html builder.
 *
 * @return \Yajra\DataTables\Html\Builder
 */
    public function html()
    {
        return $this->builder()
            ->setTableId('data-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->postAjax()
            ->language('/vendor/datatables-portuguese.json')
            ->orderBy(0, 'asc')
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
            Column::make('ticker')->title('Ticker'),
            Column::make('name')->title('Nome'),
            Column::make('type')->title('Tipo'),
            Column::computed('action')
                ->width(60)
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->title('Ações'),
        ];
    }
}
