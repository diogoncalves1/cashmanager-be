<?php
namespace Modules\Debts\DataTables;

use Modules\Accounts\Core\Helpers;
use Modules\Debts\Entities\DebtPayment;
use Modules\Debts\Entities\DebtPaymentView;
use Modules\Debts\Repositories\DebtPaymentRepository;
use Modules\Debts\Repositories\DebtRepository;
use Yajra\DataTables\Services\DataTable;

class DebtPaymentDataTable extends DataTable
{
    protected DebtPaymentRepository $repository;
    protected DebtRepository $debtRepository;

    public function __construct(DebtPaymentRepository $repository, DebtRepository $debtRepository)
    {
        $this->repository     = $repository;
        $this->debtRepository = $debtRepository;
    }

    public function dataTable($query)
    {
        $request = request();

        $user = $request->user();

        return datatables()
            ->eloquent($query)
            ->addColumn('statusTranslated', fn(DebtPayment $transaction) => __('accounts::attributes.transactions.status.' . $transaction->status))
            ->addColumn('amountFormated', fn(DebtPayment $transaction) => Helpers::formatMoneyWithSymbolAndCurrency($transaction->amount, $transaction->currencyCode, $transaction->currencySymbol))
            ->addColumn('actions', function (DebtPayment $transaction) use ($user) {
                $debt       = $transaction->debt;
                $sharedRole = $debt->userSharedRole($debt, $user->id);

                $canConfirm = $transaction->status == 'pending' && $transaction->date <= date('Y-m-d') && $sharedRole?->hasPermission("confirmTransaction");
                $canView    = $sharedRole?->hasPermission('viewTransaction');
                $canEdit    = $sharedRole?->hasPermission("editTransaction");
                $canDestroy = $sharedRole?->hasPermission("destroyTransaction");

                return ['view' => $canView, 'confirm' => $canConfirm, 'edit' => $canEdit, 'destroy' => $canDestroy];
            });
    }

    public function query(DebtPaymentView $model)
    {
        $request = request();

        $user = $request->user();

        $query = $model->newQuery()
            ->join('debts_view AS dv', 'dv.id', '=', 'debt_payments_view.debtId')
            ->whereRaw("FIND_IN_SET(?, REPLACE(dv.userIds, ' ', ''))", [$user->id])
            ->select('debt_payments_view.*');

        if ($request->has('type')) {
            $query->type($request->get('type'));
        }
        if ($request->has('debtId')) {
            $query->debt($request->get('debtId'));
        }
        if ($request->has("user")) {
            $query->user($request->get("user"));
        }

        return $query;
    }
}

//   $user = $request->user();

//         // App::setLocale($user->preferences->lang ?? 'en');

//         $query = DebtPayment::query();

//         if ($search = $request->input('search.value')) {
//             $query->where(function ($q) use ($search) {
//                 $q->where('name', 'like', "%{$search}%")
//                     ->orWhere("type", 'like', "%{$search}%")
//                     ->orWhere("balance", 'like', "%{$search}%");
//             });
//         }

//         if ($request->get('status')) {
//             $active = $request->get('status') == 'active' ? 1 : 0;
//             $query->active($active);
//         }
//         if ($request->get('type')) {
//             $query->type($request->get('type'));
//         }

//         $orderColumnIndex = $request->input('order.0.column');
//         $orderColumn      = $request->input("columns.$orderColumnIndex.data");
//         $orderDir         = $request->input('order.0.dir');
//         if ($orderColumn && $orderDir) {
//             $query->orderBy($orderColumn, $orderDir);
//         }

//         $debtPayments = $query->offset($request->start)
//             ->limit($request->length)
//             ->whereHas("users", function ($query) use ($user) {
//                 $query->where('user_id', $user->id);
//             })
//             ->distinct()
//             ->get();

//         $total = $query->count();

//         foreach ($debtPayments as &$payment) {
//             // $account->icon = Helpers::getAccountIcon($account->type);
//             $payment->typeTranslated = __("frontend." . $payment->type);
//             $payment->user           = $payment->users->map(function ($user) {
//                 return $user->name;
//             });
//             $payment->currencySymbol = $payment->currency->symbol;

//             $payment->statusTranslated = $payment->active ? __('portal.active') : __('portal.inactive');

//             $sharedRole = $debt->userSharedRole($payment, $user->id);

//             // $account->balaceFormatted = Helpers::formatMoneyWithSymbol($account->balance);

//             $btnGroup = '<div class="d-flex justify-content-center gap-1">';
//             if ($sharedRole->hasPermission("viewDebtPaymentDetails")) {
//                 $btnGroup .= '<a href="debt-payments/' . $payment->id . '" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-eye fs-lg"></i></a>';
//             }

//             if ($sharedRole->hasPermission("editDebtPayment")) {
//                 $btnGroup .= '<a href="debt-payments/' . $payment->id . '/edit" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-edit fs-lg"></i></a>';
//             }

//             if ($sharedRole->hasPermission("deleteDebtPayment")) {
//                 $btnGroup .= "<button type='button' onclick='modalDelete({$payment->id})'
//                                 data-table-delete-row
//                                  class='btn btn-light btn-icon btn-sm rounded-circle'>
//                                 <i class='ti ti-trash fs-lg'></i>
//                             </button>";
//             }

//             $btnGroup .= "</div>";
//             $payment->input   = '<input data-id="' . $payment->id . '" class="form-check-input form-check-input-light fs-14 file-item-check mt-0" type="checkbox">';
//             $payment->actions = $btnGroup;
//         }

//         return response()->json([
//             'draw'            => intval($request->draw),
//             'recordsTotal'    => $total,
//             'recordsFiltered' => $total,
//             'data'            => $debtPayments,
//         ]);
