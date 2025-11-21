<?php
namespace Modules\Accounts\DataTables;

use Modules\Accounts\Core\Helpers;
use Modules\Accounts\Entities\TransactionsView;
use Modules\Accounts\Repositories\AccountRepository;
use Modules\Accounts\Repositories\TransactionRepository;
use Yajra\DataTables\Services\DataTable;

class TransactionDataTable extends DataTable
{
    protected TransactionRepository $repository;
    protected AccountRepository $accountRepository;

    public function __construct(TransactionRepository $repository, AccountRepository $accountRepository)
    {
        $this->repository        = $repository;
        $this->accountRepository = $accountRepository;
    }

    public function dataTable($query)
    {
        $request = request();

        $user = $request->user();

        return datatables()
            ->eloquent($query)
            ->addColumn('statusTranslated', fn(TransactionsView $transaction) => __('accounts::attributes.transactions.status.' . $transaction->status))
            ->addColumn('amountFormated', fn(TransactionsView $transaction) => Helpers::formatMoneyWithSymbolAndCurrency($transaction->amount, $transaction->currencyCode, $transaction->currencySymbol))
            ->addColumn('actions', function (TransactionsView $transaction) use ($user) {
                $account    = $this->accountRepository->show($transaction->accountId);
                $sharedRole = $account->userSharedRole($account, $user->id);

                $canConfirm = $transaction->status == 'pending' && $transaction->date <= date('Y-m-d') && $sharedRole?->hasPermission("confirmTransaction");
                $canView    = $sharedRole?->hasPermission('viewTransaction');
                $canEdit    = $sharedRole?->hasPermission("editTransaction");
                $canDestroy = $sharedRole?->hasPermission("destroyTransaction");

                return ['view' => $canView, 'confirm' => $canConfirm, 'edit' => $canEdit, 'destroy' => $canDestroy];
            })
            ->editColumn('categoryName', fn(TransactionsView $transaction) => isset($transaction->categoryName->$user->preferences->lang) ? $transaction->categoryName->{$user->preferences->lang} : $transaction->categoryName->en);
    }

    public function query(TransactionsView $model)
    {
        $request = request();

        $user = $request->user();

        $query = $model->newQuery()
            ->join('accounts_view AS av', 'av.id', '=', 'transactions_view.accountId')
            ->whereRaw("FIND_IN_SET(?, REPLACE(av.user_ids, ' ', ''))", [$user->id])
            ->select('transactions_view.*');

        if ($request->has('type')) {
            $query->type($request->get('type'));
        }
        if ($request->has('accountId')) {
            $query->account($request->get('accountId'));
        }
        if ($request->has("user")) {
            $query->user($request->get("user"));
        }

        return $query;
    }
}
