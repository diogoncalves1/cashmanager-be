<?php
namespace Modules\FinancialGoal\DataTables;

use Modules\Accounts\Core\Helpers;
use Modules\FinancialGoal\Core\Helpers as CoreHelpers;
use Modules\FinancialGoal\Entities\FinancialGoalView;
use Modules\User\Http\Resources\UserShareCollection;
use Yajra\DataTables\Services\DataTable;

class FinancialGoalDataTable extends DataTable
{

    public function dataTable($query)
    {
        $request = request();

        $user = $request->user();

        return datatables()
            ->eloquent($query)
            ->addColumn('totalAmountFormated', fn(FinancialGoalView $financialGoal) => Helpers::formatMoneyWithSymbolAndCurrency($financialGoal->totalAmount, $financialGoal->currencyCode, $financialGoal->currencySymbol))
            ->addColumn('contributedAmountFormated', fn(FinancialGoalView $financialGoal) => Helpers::formatMoneyWithSymbolAndCurrency($financialGoal->contributedAmount, $financialGoal->currencyCode, $financialGoal->currencySymbol))
            ->addColumn('percentageCompeted', fn(FinancialGoalView $financialGoal) => CoreHelpers::percentage($financialGoal->totalAmount, $financialGoal->contributedAmount))
            ->addColumn('priorityTranslated', fn(FinancialGoalView $financialGoal) => __('financialgoal::attributes.financial-goals.priority.' . $financialGoal->priority))
            ->addColumn('statusTranslated', fn(FinancialGoalView $financialGoal) => __('financialgoal::attributes.financial-goals.status.' . $financialGoal->status))
            ->addColumn('users', fn(FinancialGoalView $financialGoal) => new UserShareCollection($financialGoal->users))
            ->addColumn('actions', function (FinancialGoalView $financialGoal) use ($user) {
                $sharedRole = $financialGoal->userSharedRole($financialGoal, $user->id);

                $canView          = $sharedRole?->hasPermission("viewFinancialGoal");
                $canEdit          = $sharedRole?->hasPermission("updateFinancialGoal");
                $canDestroy       = $sharedRole?->hasPermission("destroyFinancialGoal");
                $canManage        = $sharedRole?->hasPermission("manageFinancialGoalUsers");
                $canMarkCompleted = $canEdit && $financialGoal->status == 'in_progress' && $financialGoal->contributedAmount >= $financialGoal->totalAmount;

                return ['view' => $canView, 'edit' => $canEdit, 'destroy' => $canDestroy, 'manage' => $canManage, 'markCompleted' => $canMarkCompleted];
            })
            ->removeColumn('userIds')
            ->removeColumn('currencyId')
            ->removeColumn('currencyCode')
            ->removeColumn('canceledAt')
            ->removeColumn('completedAt');

    }

    public function query(FinancialGoalView $model)
    {
        $request = request();

        $user = $request->user();

        $query = $model->newQuery()
            ->whereRaw("FIND_IN_SET(?, REPLACE(userIds, ' ', ''))", [$user->id]);

        if ($request->has('status')) {
            $query->status($request->get('status'));
        }
        if ($request->has('priority')) {
            $query->priority($request->get('priority'));
        }

        return $query;
    }

    public function getQuery()
    {
        return $this->query(new FinancialGoalView());
    }
}
