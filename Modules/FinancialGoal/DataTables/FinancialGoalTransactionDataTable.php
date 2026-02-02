<?php
namespace Modules\FinancialGoal\DataTables;

use Carbon\Carbon;
use Modules\Accounts\Core\Helpers;
use Modules\FinancialGoal\Entities\FinancialGoalTransactionView;
use Modules\FinancialGoal\Repositories\FinancialGoalTransactionRepository;
use Yajra\DataTables\Services\DataTable;

class FinancialGoalTransactionDataTable extends DataTable
{
    protected $repository;

    public function __construct(FinancialGoalTransactionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function dataTable($query)
    {
        $request = request();

        $user = $request->user();

        return datatables()
            ->eloquent($query)
            ->addColumn('amountFormated', fn(FinancialGoalTransactionView $transaction) => Helpers::formatMoneyWithSymbolAndCurrency($transaction->amount, $transaction->currencyCode, $transaction->currencySymbol))
            ->addColumn('typeTranslated', fn(FinancialGoalTransactionView $transaction) => __('financialgoal::attributes.financial-goal-transactions.type.' . $transaction->type))
            ->addColumn('statusTranslated', fn(FinancialGoalTransactionView $transaction) => __('financialgoal::attributes.financial-goal-transactions.status.' . $transaction->status))
            ->addColumn('actions', function (FinancialGoalTransactionView $transaction) use ($user) {
                $sharedRole = $transaction->financialGoal->userSharedRole($transaction->financialGoal, $user->id);

                $canView    = $sharedRole->hasPermission('viewFinancialGoalTransaction');
                $canEdit    = $sharedRole->hasPermission('updateFinancialGoalTransaction');
                $canDestroy = $sharedRole->hasPermission('destroyFinancialGoalTransaction');
                $canConfirm = ($transaction->status == 'pending' && $transaction->date < Carbon::now()) && $sharedRole->hasPermission('confirmScheduledFinancialGoalTransactions');

                return ['view' => $canView, 'edit' => $canEdit, 'destroy' => $canDestroy, 'confirm' => $canConfirm];
            })
            ->removeColumn('currencyId')
            ->removeColumn('financialGoalId')
            ->removeColumn('userId')
            ->removeColumn('currencyCode');
    }

    public function query(FinancialGoalTransactionView $model)
    {
        $request = request();

        $user = $request->user();

        $query = $model->newQuery()
            ->join('financial_goal_view AS fgv', 'fgv.id', '=', 'financial_goal_transaction_view.financialGoalId')
            ->whereRaw("FIND_IN_SET(?, REPLACE(fgv.userIds, ' ', ''))", [$user->id])
            ->select('financial_goal_transaction_view.*')
            ->orderBy("id", 'desc');

        if ($request->has('userId')) {
            $query->user($request->get("userId"));
        }
        if ($request->has('financialGoalId')) {
            $query->financialGoal($request->get('financialGoalId'));
        }
        if ($request->has('type')) {
            $query->type($request->get('type'));
        }
        if ($request->has('status')) {
            $query->status($request->get('status'));
        }

        return $query;
    }
}
