<?php

namespace Modules\Friends\DataTables;

use Modules\Friends\Entities\FriendshipRequestModel;
use Modules\Friends\Repositories\FriendshipRepository;
use Modules\Friends\Repositories\FriendshipRequestRepository;
use Modules\User\Http\Resources\UserResource;
use Yajra\DataTables\Services\DataTable;

class FriendshipRequestDataTable extends DataTable
{
    protected FriendshipRequestRepository $repository;
    public $type;

    public function __construct(FriendshipRequestRepository $repository, string $type = 'pending')
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
            ->addColumn('user', function (FriendshipRequestModel $friendship) use ($user) {
                return ($friendship->sender_id == $user->id) ? new UserResource($friendship->receiver) : new UserResource($friendship->sender);
            })
            ->addColumn('actions', function () {
                return ($this->type == 'pending') ? ['accept' => true, 'decline' => true] : [];
            })
            ->removeColumn('sender_id')
            ->removeColumn('receiver_id');
    }

    public function query(FriendshipRequestModel $model)
    {
        $request = request();

        $user = $request->user();

        return $model->newQuery()->where(function ($query) use ($user) {
            return $query->sender($user->id)->orWhere('receiver_id', $user->id);
        })->status($this->type);
    }
}
