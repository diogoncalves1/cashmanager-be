<?php

namespace Modules\FinancialGoal\Paginates;

use App\Paginates\ModulePaginate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Modules\FinancialGoal\Entities\FinancialGoalView;
use Modules\FinancialGoal\Filters\FinancialGoalFilter;
use Modules\FinancialGoal\Http\Resources\Lists\FinancialGoalListResource;
use Modules\FinancialGoal\Searches\FinancialGoalSearch;
use Modules\User\Entities\User;

class FinancialGoalPaginate extends ModulePaginate
{
    public const RESOURCE = FinancialGoalListResource::class;

    protected array $allowedOrderFields = ['name' => 'financial_goal_view.name', 'total_amount' => 'financial_goal_view.totalAmount', 'contributed_amount' => 'financial_goal_view.contributedAmount', 'priority' => 'financial_goal_view.priority', 'status' => 'financial_goal_view.status', 'id' => 'financial_goal_view.id'];

    protected string $defaultOrderField = 'financial_goal_view.id';

    protected string $defaultSort = 'asc';

    public function __construct(FinancialGoalFilter $filter, FinancialGoalSearch $search)
    {
        parent::__construct($filter, $search);
    }

    protected function query(User $user, Request $request): Builder|Relation
    {
        return $this->visibleTo(FinancialGoalView::query(), $user, 'financial_goal_users', 'financial_goal_id', 'financial_goal_view.id', 'viewFinancialGoal')->with(['financialGoal', 'users']);
    }
}
