<?php

namespace Modules\Friends\DataTables;

use Modules\Friends\Entities\Friendship;
use Modules\Friends\Repositories\FriendshipRepository;
use Modules\User\Http\Resources\UserResource;
use Yajra\DataTables\Services\DataTable;

class FriendshipDataTable extends DataTable
{
    protected FriendshipRepository $repository;
    public $type;

    public function __construct(FriendshipRepository $repository, string $type = 'friend')
    {
        $this->repository = $repository;
        $this->type = $type;
    }

    public function dataTable($query)
    {
        $request = request();

        $user = $request->user();

        return datatables()
            ->eloquent($query)
            ->addColumn('user', function (Friendship $friendship) use ($user) {
                return ($friendship->sender_id == $user->id) ? new UserResource($friendship->receiver) : new UserResource($friendship->sender);
            })
            ->addColumn('actions', function () {
                return ($this->type == 'friend') ? ['remove' => true, 'block' => true] : ['unblock' => true];
            })
            ->removeColumn('sender_id')
            ->removeColumn('receiver_id');
    }

    public function query(Friendship $model)
    {
        $request = request();

        $user = $request->user();

        return $model->newQuery()->where(function ($query) use ($user) {
            return $query->sender($user->id)->orWhere('receiver_id', $user->id);
        })->status($this->type);
    }
}
