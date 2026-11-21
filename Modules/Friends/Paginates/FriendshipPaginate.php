<?php

namespace Modules\Friends\Paginates;

use App\Paginates\ModulePaginate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Modules\Friends\Entities\Friendship;
use Modules\Friends\Filters\FriendshipFilter;
use Modules\Friends\Http\Resources\Lists\FriendshipListResource;
use Modules\Friends\Searches\FriendshipSearch;
use Modules\User\Entities\User;

class FriendshipPaginate extends ModulePaginate
{
    public const RESOURCE = FriendshipListResource::class;

    protected array $allowedOrderFields = ['id' => 'friendships.id', 'created_at' => 'friendships.created_at'];

    protected string $defaultOrderField = 'friendships.id';

    protected string $defaultSort = 'desc';

    public string $type = 'friend';

    public function __construct(FriendshipFilter $filter, FriendshipSearch $search)
    {
        parent::__construct($filter, $search);
    }

    protected function query(User $user, Request $request): Builder|Relation
    {
        return Friendship::query()->where(fn ($query) => $query->where('sender_id', $user->id)->orWhere('receiver_id', $user->id))
            ->where('status', $this->type)->with(['sender', 'receiver']);
    }
}
