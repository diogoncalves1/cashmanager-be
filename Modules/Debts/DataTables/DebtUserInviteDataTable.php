<?php
namespace Modules\Debts\DataTables;

use Carbon\Carbon;
use Modules\Debts\Entities\DebtUserInvite;
use Modules\Debts\Http\Resources\DebtResource;
use Modules\SharedRoles\Http\Resources\SharedRoleResource;
use Modules\User\Http\Resources\UserShareResource;
use Yajra\DataTables\Services\DataTable;

class DebtUserInviteDataTable extends DataTable
{
    public string $type;

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('receiver', fn(DebtUserInvite $invite) => new UserShareResource($invite->user))
            ->addColumn('sender', fn(DebtUserInvite $invite) => new UserShareResource($invite->sender))
            ->addColumn('subject', fn(DebtUserInvite $invite) => new DebtResource($invite->debt))
            ->addColumn('sharedRole', fn(DebtUserInvite $invite) => new SharedRoleResource($invite->sharedRole))
            ->addColumn('createdAt', function (DebtUserInvite $invite) {
                $date = new Carbon($invite->created_at);

                return $date->format('Y-m-d');
            })
            ->addColumn('statusTranslated', fn(DebtUserInvite $invite) => __('financialgoal::attributes.financial-goal-user-invites.status.' . $invite->status))
            ->removeColumn('user_id')
            ->removeColumn('invited_by_id')
            ->removeColumn('shared_role_id')
            ->removeColumn('debt_id')
            ->removeColumn('created_at')
            ->removeColumn('updated_at');

    }

    public function query(DebtUserInvite $model)
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
