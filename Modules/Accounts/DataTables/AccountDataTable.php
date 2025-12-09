<?php
namespace Modules\Accounts\DataTables;

use Modules\Accounts\Core\Helpers;
use Modules\Accounts\Entities\AccountsView;
use Modules\Accounts\Repositories\AccountRepository;
use Modules\User\Http\Resources\UserShareCollection;
use Yajra\DataTables\Services\DataTable;

class AccountDataTable extends DataTable
{
    protected AccountRepository $repository;

    public function __construct(AccountRepository $repository)
    {
        $this->repository = $repository;
    }

    public function dataTable($query)
    {
        $request = request();

        $user = $request->user();

        return datatables()
            ->eloquent($query)
            ->addColumn('typeTranslated', fn(AccountsView $account) => __('accounts::attributes.accounts.type.' . $account->type))
            ->addColumn('balanceFormated', fn(AccountsView $account) => Helpers::formatMoneyWithSymbolAndCurrency($account->balance, $account->currencyCode, $account->currencySymbol))
            ->addColumn('statusTranslated', fn(AccountsView $account) => __("accounts::attributes.accounts.status." . ($account->status ? 'active' : 'disabled')))
            ->addColumn('users', fn(AccountsView $account) => new UserShareCollection($account->users))
            ->addColumn('actions', function (AccountsView $account) use ($user) {
                $account    = $this->repository->show($account->id);
                $sharedRole = $account->userSharedRole($account, $user->id);

                $canView    = $sharedRole?->hasPermission("viewAccount");
                $canEdit    = $sharedRole?->hasPermission("editAccount");
                $canDestroy = $sharedRole?->hasPermission("destroyAccount");
                $canManage  = $sharedRole?->hasPermission("manageAccountUsers");

                return ['view' => $canView, 'edit' => $canEdit, 'destroy' => $canDestroy, 'manage' => $canManage];
            })
            ->removeColumn('user_ids');
    }

    public function query(AccountsView $model)
    {
        $request = request();

        $user = $request->user();

        $query = $model->newQuery()
            ->whereRaw("FIND_IN_SET(?, REPLACE(user_ids, ' ', ''))", [$user->id]);

        if ($request->has('type')) {
            $query->type($request->get('type'));
        }
        if ($request->has('status')) {
            $query->active($request->get('status') == 'active' ? 1 : 0);
        }

        return $query;
    }
}
