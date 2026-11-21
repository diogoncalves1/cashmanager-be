<?php

namespace Modules\Debts\Paginates;

use App\Paginates\ModulePaginate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Debts\Entities\DebtsView;
use Modules\Debts\Filters\DebtFilter;
use Modules\Debts\Http\Resources\Lists\DebtListResource;
use Modules\Debts\Searches\DebtSearch;
use Modules\User\Entities\User;

class DebtPaginate extends ModulePaginate
{
    public const RESOURCE = DebtListResource::class;

    protected array $allowedOrderFields = ['name' => 'debts_view.name', 'total_amount' => 'debts_view.totalAmount', 'interest_rate' => 'debts_view.interestRate', 'paid_amount' => 'debts_view.paidAmount', 'status' => 'debts_view.status', 'id' => 'debts_view.id'];

    protected string $defaultOrderField = 'debts_view.id';

    protected string $defaultSort = 'asc';

    public function __construct(DebtFilter $filter, DebtSearch $search)
    {
        parent::__construct($filter, $search);
        $this->allowedOrderFields['remaining_amount'] = DB::raw('(debts_view.totalAmount * (1 + debts_view.interestRate / 100.0 * debts_view.months) - debts_view.paidAmount)');
    }

    protected function query(User $user, Request $request): Builder|Relation
    {
        return $this->visibleTo(DebtsView::query(), $user, 'debt_users', 'debt_id', 'debts_view.id', 'viewDebt')->with(['currency', 'users', 'debt']);
    }
}
