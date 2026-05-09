<?php
namespace Modules\EmailLayout\DataTables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Modules\EmailLayout\Entities\EmailType;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class EmailLayoutDataTable extends DataTable
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

        $canEdit = $user->can('authorization', 'editEmailLayout');

        return datatables()
            ->eloquent($query)
            ->addColumn('subject', fn(EmailType $emailType) => $emailType->emailLayout()->first() ? $emailType->emailLayout()->first()->subject : '')
            ->addColumn('action', function (EmailType $emailType) use ($canEdit) {
                $btn = '<div class="hstack gap-2 justify-content-end">';

                if ($canEdit) {
                    $btn .= '<a title=\'Editar\'
                data-toggle="tooltip" data-placement="top"
                class="btn btn-default mr-1"
                href="' . route("admin.settings.emailLayouts.edit", $emailType->id) . '">
                    <span class="m-l-5"><i class="fa fa-pencil-alt"></i></span></a>';
                }
                $btn .= '</div>';

                return $btn;
            })->rawColumns(['roles', 'action']);

    }

    /**
     * Get query source of dataTable.
     *
     * @param EmailType $model
     * @return Builder
     */
    public function query(EmailType $model)
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('datatable-responsive')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'asc')
            ->renderer('bootstrap')
            ->pagingType('simple_numbers')
            ->dom('Bfrtip')
            ->language('/vendor/datatables-portuguese.json')
            ->drawCallback(" function () {
                    $('[data-toggle=\"tooltip\"]').tooltip();
                }");
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
            Column::make('subject')->title('Assunto'),
            Column::computed('action')
                ->width(60)
                ->orderable(false)
                ->exportable(false)
                ->printable(false)
                ->title('Ações'),
        ];

    }

}
