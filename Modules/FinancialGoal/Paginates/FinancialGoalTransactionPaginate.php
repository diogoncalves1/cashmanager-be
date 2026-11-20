<?php

namespace Modules\FinancialGoal\Paginates;

use App\Paginates\ModulePaginate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Modules\FinancialGoal\Entities\FinancialGoalTransactionView;
use Modules\FinancialGoal\Filters\FinancialGoalTransactionFilter;
use Modules\FinancialGoal\Http\Resources\Lists\FinancialGoalTransactionListResource;
use Modules\FinancialGoal\Searches\FinancialGoalTransactionSearch;
use Modules\User\Entities\User;

class FinancialGoalTransactionPaginate extends ModulePaginate
{
    public const RESOURCE = FinancialGoalTransactionListResource::class;

    protected array $allowedOrderFields = ['date' => 'financial_goal_transaction_view.date', 'amount' => 'financial_goal_transaction_view.amount', 'type' => 'financial_goal_transaction_view.type', 'status' => 'financial_goal_transaction_view.status', 'id' => 'financial_goal_transaction_view.id'];

    protected string $defaultOrderField = 'financial_goal_transaction_view.id';

    protected string $defaultSort = 'asc';

    public function __construct(FinancialGoalTransactionFilter $filter, FinancialGoalTransactionSearch $search)
    {
        parent::__construct($filter, $search);
    }

    protected function query(User $user, Request $request): Builder|Relation
    {
        return $this->visibleTo(FinancialGoalTransactionView::query(), $user, 'financial_goal_users', 'financial_goal_id', 'financial_goal_transaction_view.financialGoalId', 'viewFinancialGoalTransaction')->with('financialGoal.users');
    }
}
