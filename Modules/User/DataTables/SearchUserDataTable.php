<?php
namespace Modules\User\DataTables;

use Modules\Friends\Repositories\FriendshipRepository;
use Modules\Friends\Repositories\FriendshipRequestRepository;
use Modules\User\Entities\User;
use Yajra\DataTables\Services\DataTable;

class SearchUserDataTable extends DataTable
{
    public function __construct(
        protected FriendshipRepository $friendshipRepo,
        protected FriendshipRequestRepository $friendshipRequestRepo
    ) {

    }

    public function dataTable($query)
    {
        $request = request();
        $user    = $request->user();

        return datatables()
            ->eloquent($query)
            ->addColumn('status', function (User $userS) use ($user) {
                if ($this->friendshipRepo->isBlocked($userS->id, $user->id)) {
                    return 'blocked';
                }
                if ($this->friendshipRepo->areFriends($userS->id, $user->id)) {
                    return 'friend';
                }
                if ($this->friendshipRequestRepo->hasPendingRequest($userS->id, $user->id)) {
                    return 'pending';
                }
                return '';

            })
            ->addColumn('statusTranslated', fn(User $invite) => __('financialgoal::attributes.financial-goal-user-invites.status.' . $invite->status))
            ->removeColumn('password')
            ->removeColumn('username_updated_at')
            ->removeColumn('email')
            ->removeColumn('created_at')
            ->removeColumn('updated_at');

    }

    public function query(User $model)
    {
        $request = request();

        $user = $request->user();

        return $model->newQuery()
            ->where("id", '!=', $user->id)
            ->orderBy("created_at", 'desc')
            ->orderBy('id', 'desc');

    }
}
