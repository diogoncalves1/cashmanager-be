<?php

namespace Modules\Accounts\Paginates;

use App\Paginates\ModulePaginate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Modules\Accounts\Entities\Account;
use Modules\Accounts\Filters\AccountFilter;
use Modules\Accounts\Http\Resources\Accounts\Lists\AccountListResource;
use Modules\Accounts\Searches\AccountSearch;
use Modules\User\Entities\User;

class AccountPaginate extends ModulePaginate
{
    public const RESOURCE = AccountListResource::class;

    protected array $allowedOrderFields = ['name' => 'accounts.name', 'type' => 'accounts.type', 'balance' => 'accounts.balance', 'id' => 'accounts.id'];

    protected string $defaultOrderField = 'accounts.id';

    protected string $defaultSort = 'asc';

    public function __construct(AccountFilter $filter, AccountSearch $search)
    {
        parent::__construct($filter, $search);
    }

    protected function query(User $user, Request $request): Builder|Relation
    {
        return $this->visibleTo(Account::query(), $user, 'account_users', 'account_id', 'accounts.id', 'viewAccount')
            ->with(['currency', 'users'])->withCount('transactions');
    }
}
