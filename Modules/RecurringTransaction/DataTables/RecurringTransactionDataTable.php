<?php
namespace Modules\RecurringTransaction\DataTables;

use Modules\Accounts\Core\Helpers;
use Modules\Category\Http\Resources\CategoryResource;
use Modules\RecurringTransaction\Entities\RecurringTransaction;
use Modules\RecurringTransaction\Filters\RecurringTransactionFilter;
use Yajra\DataTables\Services\DataTable;

class RecurringTransactionDataTable extends DataTable
{
    protected array $linkedTypes = [
        'Modules\Debts\Entities\Debt'                  => 'debt',
        'Modules\FinancialGoal\Entities\FinancialGoal' => 'financial_goal',
    ];

    public function dataTable($query)
    {
        $request = request();

        $user = $request->user();

        return datatables()
            ->eloquent($query)
            ->editColumn('amount', fn(RecurringTransaction $transaction) => (float) $transaction->amount)
            ->addColumn('amountFormated', fn(RecurringTransaction $transaction) => Helpers::formatMoneyWithSymbolAndCurrency($transaction->amount, $transaction->account->currency->code, $transaction->account->currency->symbol))
            ->addColumn('lastGenerated', fn(RecurringTransaction $transaction) => $transaction->last_generated_at ? $transaction->last_generated_at->format('Y-m-d') : null)
            ->addColumn('nextOccurrence', fn(RecurringTransaction $transaction) => $transaction->next_occurrence)
            ->orderColumn('nextOccurrence', function ($query, $order) {
                $query->orderByRaw('COALESCE(recurring_transactions.last_generated_at, recurring_transactions.start_date) '.$order);
            })
            ->addColumn('currencySymbol', fn(RecurringTransaction $transaction) => $transaction->account->currency->symbol)
            ->addColumn('currencyCode', fn(RecurringTransaction $transaction) => $transaction->account->currency->code)
            ->addColumn('isActive', fn(RecurringTransaction $transaction) => (bool) $transaction->is_active)
            ->addColumn('customInterval', fn(RecurringTransaction $transaction) => $transaction->custom_frequency_days)
            ->addColumn('categoryName', fn(RecurringTransaction $transaction) => $transaction->category ? (new CategoryResource($transaction->category))->resolve($request)['name'] : null)
            ->addColumn('categoryIcon', fn(RecurringTransaction $transaction) => $transaction->category?->icon)
            ->addColumn('categoryColor', fn(RecurringTransaction $transaction) => $transaction->category?->color)
            ->addColumn('accountName', fn(RecurringTransaction $transaction) => $transaction->account->name)
            ->addColumn('accountType', fn(RecurringTransaction $transaction) => $transaction->account->type)
            ->addColumn('actions', function (RecurringTransaction $transaction) use ($user) {
                return [
                    'view' => $user->can('show', $transaction),
                    'edit' => $user->can('update', $transaction),
                    'toggle' => $user->can('update', $transaction),
                    'destroy' => $user->can('delete', $transaction),
                ];
            })
            ->addColumn('isLinked', fn(RecurringTransaction $transaction) => (bool) $transaction->is_linked)
            ->editColumn('linkedName', fn(RecurringTransaction $transaction) => $transaction->is_linked ? $transaction->linked?->name : null)
            ->addColumn('linked', fn(RecurringTransaction $transaction) => $transaction->is_linked && $transaction->linked ? ['id' => $transaction->linked->id, 'name' => $transaction->linked->name] : null)
            ->editColumn('linkedType', fn(RecurringTransaction $transaction) => $transaction->is_linked ? $this->linkedTypes[$transaction->linked_type] : null)
            ->editColumn('userName', fn(RecurringTransaction $transaction) => $transaction->user->name)
            ->editColumn('category', fn(RecurringTransaction $transaction) => new CategoryResource($transaction->category))
            ->removeColumn(['account_id', 'account', 'user', 'category_id', 'user_id', 'linked_type', 'linked_id', 'created_at', 'updated_at', 'deleted_at', 'is_linked', 'last_generated_at', 'is_active', 'end_date', 'custom_frequency_days', 'start_date']);
    }

    public function query(RecurringTransaction $model)
    {
        $request = request();

        $user = $request->user();

        $query = $model->newQuery()
            ->with(['category', 'account', 'user', 'account.currency', 'linked'])
            ->join('accounts_view AS av', 'av.id', '=', 'recurring_transactions.account_id')
            ->whereRaw("FIND_IN_SET(?, REPLACE(av.user_ids, ' ', ''))", [$user->id])
            ->select('recurring_transactions.*');

        $query = (new RecurringTransactionFilter)->filter($request, $query);

        return $query;
    }
}
