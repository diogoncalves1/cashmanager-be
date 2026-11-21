<?php

namespace Modules\Accounts\Paginates;

use App\Paginates\ModulePaginate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Modules\Accounts\Entities\Transaction;
use Modules\Accounts\Filters\TransactionFilter;
use Modules\Accounts\Http\Resources\Transactions\Lists\TransactionListResource;
use Modules\Accounts\Searches\TransactionSearch;
use Modules\User\Entities\User;

class TransactionPaginate extends ModulePaginate
{
    public const RESOURCE = TransactionListResource::class;

    protected array $allowedOrderFields = ['account_id' => 'transactions.account_id', 'date' => 'transactions.date', 'amount' => 'transactions.amount', 'type' => 'transactions.type', 'status' => 'transactions.status', 'id' => 'transactions.id'];

    protected string $defaultOrderField = 'transactions.id';

    protected string $defaultSort = 'asc';

    public function __construct(TransactionFilter $filter, TransactionSearch $search)
    {
        parent::__construct($filter, $search);
    }

    protected function query(User $user, Request $request): Builder|Relation
    {
        return $this->visibleTo(Transaction::query(), $user, 'account_users', 'account_id', 'transactions.account_id', 'viewTransaction')
            ->with(['account.currency', 'account.users', 'user', 'category']);
    }
}
