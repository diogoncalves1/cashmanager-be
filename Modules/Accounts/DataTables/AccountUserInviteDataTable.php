<?php
namespace Modules\FinancialGoal\DataTables;

use Carbon\Carbon;
use Modules\Accounts\Entities\AccountUserInvite;
use Modules\Debts\Http\Resources\DebtResource;
use Modules\SharedRoles\Http\Resources\SharedRoleResource;
use Modules\User\Http\Resources\UserShareResource;
use Yajra\DataTables\Services\DataTable;

class AccountUserInviteDataTable extends DataTable
{
    public string $type;

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('receiver', fn(AccountUserInvite $invite) => new UserShareResource($invite->user))
            ->addColumn('sender', fn(AccountUserInvite $invite) => new UserShareResource($invite->sender))
            ->addColumn('subject', fn(AccountUserInvite $invite) => new DebtResource($invite->account))
            ->addColumn('sharedRole', fn(AccountUserInvite $invite) => new SharedRoleResource($invite->sharedRole))
            ->addColumn('createdAt', fn(AccountUserInvite $invite) => new Carbon($invite->created_at)->format('Y-m-d'))
            ->addColumn('statusTranslated', fn(AccountUserInvite $invite) => __('financialgoal::attributes.financial-goal-user-invites.status.' . $invite->status))
            ->removeColumn('user_id')
            ->removeColumn('invited_by_id')
            ->removeColumn('shared_role_id')
            ->removeColumn('account_id')
            ->removeColumn('created_at')
            ->removeColumn('updated_at');

    }

    public function query(AccountUserInvite $model)
    {
        $request = request();

        $user = $request->user();

        $query = $model->newQuery()
            ->orderBy("created_at", 'desc')
            ->orderBy('id', 'desc');

        if ($this->type == 'sent') {
            $query->invitedBy($user->id);
        } else {
            $query->user($user->id);
        }
        if ($request->has('status') && $request->get('status') != 'all') {
            $query->status($request->get('status'));
        }

        return $query;
    }
}
