<?php

namespace Modules\Debts\Paginates;

use App\Paginates\ModulePaginate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Modules\Debts\Entities\DebtUserInvite;
use Modules\Debts\Filters\DebtUserInviteFilter;
use Modules\Debts\Http\Resources\Lists\DebtUserInviteListResource;
use Modules\Debts\Searches\DebtUserInviteSearch;
use Modules\User\Entities\User;

class DebtUserInvitePaginate extends ModulePaginate
{
    public const RESOURCE = DebtUserInviteListResource::class;

    protected array $allowedOrderFields = ['created_at' => 'debt_user_invites.created_at', 'status' => 'debt_user_invites.status', 'id' => 'debt_user_invites.id'];

    protected string $defaultOrderField = 'debt_user_invites.id';

    protected string $defaultSort = 'desc';

    public string $type = 'received';

    public function __construct(DebtUserInviteFilter $filter, DebtUserInviteSearch $search)
    {
        parent::__construct($filter, $search);
    }

    protected function query(User $user, Request $request): Builder|Relation
    {
        return DebtUserInvite::query()->where($this->type === 'sent' ? 'invited_by_id' : 'user_id', $user->id)
            ->with(['user', 'sender', 'sharedRole', 'debt']);
    }
}
