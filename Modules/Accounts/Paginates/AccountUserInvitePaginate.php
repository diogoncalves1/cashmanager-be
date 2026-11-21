<?php

namespace Modules\Accounts\Paginates;

use App\Paginates\ModulePaginate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Modules\Accounts\Entities\AccountUserInvite;
use Modules\Accounts\Filters\AccountUserInviteFilter;
use Modules\Accounts\Http\Resources\Lists\AccountUserInviteListResource;
use Modules\Accounts\Searches\AccountUserInviteSearch;
use Modules\User\Entities\User;

class AccountUserInvitePaginate extends ModulePaginate
{
    public const RESOURCE = AccountUserInviteListResource::class;

    protected array $allowedOrderFields = ['created_at' => 'account_user_invites.created_at', 'status' => 'account_user_invites.status', 'id' => 'account_user_invites.id'];

    protected string $defaultOrderField = 'account_user_invites.id';

    protected string $defaultSort = 'desc';

    public string $type = 'received';

    public function __construct(AccountUserInviteFilter $filter, AccountUserInviteSearch $search)
    {
        parent::__construct($filter, $search);
    }

    protected function query(User $user, Request $request): Builder|Relation
    {
        return AccountUserInvite::query()->where($this->type === 'sent' ? 'invited_by_id' : 'user_id', $user->id)
            ->with(['user', 'sender', 'sharedRole', 'account']);
    }
}
