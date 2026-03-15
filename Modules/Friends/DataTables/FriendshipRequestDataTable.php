<?php
namespace Modules\Friends\DataTables;

use Modules\Friends\Entities\FriendshipRequestModel;
use Modules\Friends\Repositories\FriendshipRequestRepository;
use Modules\User\Http\Resources\UserResource;
use Yajra\DataTables\Services\DataTable;

class FriendshipRequestDataTable extends DataTable
{
    protected FriendshipRequestRepository $repository;
    public $type;
    public $status;

    public function __construct(FriendshipRequestRepository $repository, string $status = 'pending', ?string $type = null)
    {
        $this->repository = $repository;
        $this->type       = $type;
        $this->status     = $status;
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
                return ($this->status == 'pending') ? ['accept' => true, 'decline' => true] : [];
            })
            ->addColumn('createdAt', fn(FriendshipRequestModel $friendship) => $friendship->created_at->format('Y-m-d'))
            ->removeColumn('sender_id')
            ->removeColumn('created_at')
            ->removeColumn('receiver_id');
    }

    public function query(FriendshipRequestModel $model)
    {
        $request = request();

        $user = $request->user();

        $query = $model->newQuery()->status($this->status);

        if ($this->type == 'sent') {
            $query->sender($user->id);
        } elseif ($this->type == 'received') {
            $query->receiver($user->id);
        } else {
            $query->where(function ($query) use ($user) {
                return $query->sender($user->id)->orWhere('receiver_id', $user->id);
            });
        }

        if ($request->has('search') && ! empty($request->get('search')['value'])) {
            $search = $request->get('search')['value'];

            $query->where(function ($q) use ($search, $user) {
                $q->whereHas('sender', function ($q2) use ($search, $user) {
                    $q2->where('id', '!=', $user->id)
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%");
                })
                    ->orWhereHas('receiver', function ($q2) use ($search, $user) {
                        $q2->where('id', '!=', $user->id)
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('id', 'like', "%{$search}%");
                    });
            });
        }

        return $query;

    }
}
