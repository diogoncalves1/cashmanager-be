<?php
namespace Modules\Debts\DataTables;

use Modules\Accounts\Core\Helpers;
use Modules\Debts\Core\Helpers as DebtHelpers;
use Modules\Debts\Entities\DebtsView;
use Modules\Debts\Repositories\DebtRepository;
use Yajra\DataTables\Services\DataTable;

class DebtDataTable extends DataTable
{
    protected DebtRepository $repository;

    public function __construct(DebtRepository $repository)
    {
        $this->repository = $repository;
    }

    public function dataTable($query)
    {
        $request = request();

        $user = $request->user();

        return datatables()
            ->eloquent($query)
            ->addColumn('interestValue', fn(DebtsView $debt) => DebtHelpers::debtInterestValue($debt->totalAmount, $debt->interestRate, $debt->months))
            ->addColumn('remainingAmount', fn(DebtsView $debt) => DebtHelpers::debtRemainingAmount($debt->totalAmount, $debt->paidAmount, $debt->interestRate, $debt->months))
            ->addColumn('totalAmountWithInterest', fn(DebtsView $debt) => DebtHelpers::debtTotalAmount($debt->totalAmount, $debt->interestRate, $debt->months))
            ->addColumn('totalAmountFormated', fn(DebtsView $debt) => Helpers::formatMoneyWithSymbolAndCurrency($debt->totalAmount, $debt->currencyCode, $debt->currencySymbol))
            ->addColumn('paidAmountFormated', fn(DebtsView $debt) => Helpers::formatMoneyWithSymbolAndCurrency($debt->paidAmount, $debt->currencyCode, $debt->currencySymbol))
            ->addColumn('monthlyAmountFormated', fn(DebtsView $debt) => Helpers::formatMoneyWithSymbolAndCurrency($debt->monthlyAmount, $debt->currencyCode, $debt->currencySymbol))
            ->addColumn('typeTranslated', fn(DebtsView $debt) => __('debts::attributes.debts.type.' . $debt->type))
            ->addColumn('statusTranslated', fn(DebtsView $debt) => __("debts::attributes.debts.status." . $debt->status))
            ->addColumn('actions', function (DebtsView $debt) use ($user) {
                $debt       = $this->repository->show($debt->id);
                $sharedRole = $debt->userSharedRole($debt, $user->id);

                $canView    = $sharedRole?->hasPermission("viewDebt");
                $canEdit    = $sharedRole?->hasPermission("editDebt");
                $canDestroy = $sharedRole?->hasPermission("destroyDebt");
                $canManage  = $sharedRole?->hasPermission("manageDebtUsers");

                return ['view' => $canView, 'edit' => $canEdit, 'destroy' => $canDestroy, 'manage' => $canManage];
            })
            ->removeColumn('user_ids');
    }

    public function query(DebtsView $model)
    {
        $request = request();

        $user = $request->user();

        $query = $model->newQuery()
            ->whereRaw("FIND_IN_SET(?, REPLACE(userIds, ' ', ''))", [$user->id]);

        if ($request->has('type')) {
            $query->type($request->get('type'));
        }
        if ($request->has('status')) {
            $query->status($request->get('status'));
        }

        return $query;
    }
}
