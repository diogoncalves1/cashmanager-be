<?php

namespace Modules\Debts\Paginates;

use App\Paginates\ModulePaginate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Modules\Debts\Entities\DebtPaymentView;
use Modules\Debts\Filters\DebtPaymentFilter;
use Modules\Debts\Http\Resources\Lists\DebtPaymentListResource;
use Modules\Debts\Searches\DebtPaymentSearch;
use Modules\User\Entities\User;

class DebtPaymentPaginate extends ModulePaginate
{
    public const RESOURCE = DebtPaymentListResource::class;

    protected array $allowedOrderFields = ['debt' => 'debt_payments_view.debtName', 'amount' => 'debt_payments_view.amount', 'interest_paid' => 'debt_payments_view.interestPaid', 'date' => 'debt_payments_view.date', 'status' => 'debt_payments_view.status', 'id' => 'debt_payments_view.id'];

    protected string $defaultOrderField = 'debt_payments_view.id';

    protected string $defaultSort = 'asc';

    public function __construct(DebtPaymentFilter $filter, DebtPaymentSearch $search)
    {
        parent::__construct($filter, $search);
    }

    protected function query(User $user, Request $request): Builder|Relation
    {
        return $this->visibleTo(DebtPaymentView::query(), $user, 'debt_users', 'debt_id', 'debt_payments_view.debtId', 'viewDebtPayment')->with('debt.users');
    }
}
