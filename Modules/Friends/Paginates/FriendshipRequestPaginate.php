<?php

namespace Modules\Friends\Paginates;

use App\Paginates\ModulePaginate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Modules\Friends\Entities\FriendshipRequestModel;
use Modules\Friends\Filters\FriendshipRequestFilter;
use Modules\Friends\Http\Resources\Lists\FriendshipRequestListResource;
use Modules\Friends\Searches\FriendshipRequestSearch;
use Modules\User\Entities\User;

class FriendshipRequestPaginate extends ModulePaginate
{
    public const RESOURCE = FriendshipRequestListResource::class;

    protected array $allowedOrderFields = ['id' => 'friendship_requests.id', 'created_at' => 'friendship_requests.created_at'];

    protected string $defaultOrderField = 'friendship_requests.id';

    protected string $defaultSort = 'desc';

    public string $type = 'received';

    public function __construct(FriendshipRequestFilter $filter, FriendshipRequestSearch $search)
    {
        parent::__construct($filter, $search);
    }

    protected function query(User $user, Request $request): Builder|Relation
    {
        return FriendshipRequestModel::query()->where('status', 'pending')
            ->where($this->type === 'sent' ? 'sender_id' : 'receiver_id', $user->id)->with(['sender', 'receiver']);
    }
}
