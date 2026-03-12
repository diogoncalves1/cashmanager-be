<?php
namespace Modules\FinancialGoal\DataTables;

use Carbon\Carbon;
use Modules\FinancialGoal\Entities\FinancialGoalUserInvite;
use Modules\FinancialGoal\Http\Resources\FinancialGoalResource;
use Modules\SharedRoles\Http\Resources\SharedRoleResource;
use Modules\User\Http\Resources\UserShareResource;
use Yajra\DataTables\Services\DataTable;

class FinancialGoalUserInviteDataTable extends DataTable
{
    public string $type;

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('receiver', fn(FinancialGoalUserInvite $invite) => new UserShareResource($invite->user))
            ->addColumn('sender', fn(FinancialGoalUserInvite $invite) => new UserShareResource($invite->sender))
            ->addColumn('subject', fn(FinancialGoalUserInvite $invite) => new FinancialGoalResource($invite->financialGoal))
            ->addColumn('sharedRole', fn(FinancialGoalUserInvite $invite) => new SharedRoleResource($invite->sharedRole))
            ->addColumn('createdAt', function (FinancialGoalUserInvite $invite) {
                $date = new Carbon($invite->created_at);

                return $date->format('Y-m-d');
            })
            ->addColumn('statusTranslated', fn(FinancialGoalUserInvite $invite) => __('financialgoal::attributes.financial-goal-user-invites.status.' . $invite->status))
            ->removeColumn('user_id')
            ->removeColumn('invited_by_id')
            ->removeColumn('shared_role_id')
            ->removeColumn('financial_goal_id')
            ->removeColumn('created_at')
            ->removeColumn('updated_at');

    }

    public function query(FinancialGoalUserInvite $model)
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
