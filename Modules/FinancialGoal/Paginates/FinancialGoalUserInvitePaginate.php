<?php

namespace Modules\FinancialGoal\Paginates;

use App\Paginates\ModulePaginate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Modules\FinancialGoal\Entities\FinancialGoalUserInvite;
use Modules\FinancialGoal\Filters\FinancialGoalUserInviteFilter;
use Modules\FinancialGoal\Http\Resources\Lists\FinancialGoalUserInviteListResource;
use Modules\FinancialGoal\Searches\FinancialGoalUserInviteSearch;
use Modules\User\Entities\User;

class FinancialGoalUserInvitePaginate extends ModulePaginate
{
    public const RESOURCE = FinancialGoalUserInviteListResource::class;

    protected array $allowedOrderFields = ['created_at' => 'financial_goal_user_invites.created_at', 'status' => 'financial_goal_user_invites.status', 'id' => 'financial_goal_user_invites.id'];

    protected string $defaultOrderField = 'financial_goal_user_invites.id';

    protected string $defaultSort = 'desc';

    public string $type = 'received';

    public function __construct(FinancialGoalUserInviteFilter $filter, FinancialGoalUserInviteSearch $search)
    {
        parent::__construct($filter, $search);
    }

    protected function query(User $user, Request $request): Builder|Relation
    {
        return FinancialGoalUserInvite::query()->where($this->type === 'sent' ? 'invited_by_id' : 'user_id', $user->id)
            ->with(['user', 'sender', 'sharedRole', 'financialGoal']);
    }
}
