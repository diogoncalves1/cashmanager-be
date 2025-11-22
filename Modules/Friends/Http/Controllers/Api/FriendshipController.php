<?php

namespace Modules\Friends\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Friends\DataTables\FriendshipDataTable;
use Modules\Friends\Http\Resources\FriendshipResource;
use Modules\Friends\Repositories\FriendshipRepository;

class FriendshipController extends ApiController
{
    private FriendshipRepository $friendshipRepository;

    public function __construct(FriendshipRepository $friendshipRepository)
    {
        $this->friendshipRepository = $friendshipRepository;
    }

    /**
     * Remove a friend.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function remove(Request $request, string $id): JsonResponse
    {
        try {
            $this->friendshipRepository->remove($request, $id);

            return $this->ok(message: __('friends::messages.friendships.remove'));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage() ?? __('friends::messages.friendships.errors.remove'), $e, $e->getCode());
        }
    }

    /**
     * Block a user.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function block(Request $request, string $id)
    {
        try {
            $friendship = $this->friendshipRepository->block($request, $id);

            return $this->ok(new FriendshipResource($friendship), __('friends::messages.friendships.block', ['name' => $friendship->receiver->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage() ?? __('friends::messages.friendships.errors.block'), $e, $e->getCode());
        }
    }

    /**
     * Unblock a friend.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function unblock(Request $request, string $id)
    {
        try {
            $friendship = $this->friendshipRepository->unblock($request, $id);

            return $this->ok(message: __('friends::messages.friendships.unblock', ['name' => $friendship->receiver->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage() ?? __('friends::messages.friendships.errors.unblock'), $e, $e->getCode());
        }
    }

    public function listFriends(FriendshipDataTable $dataTable)
    {
        try {
            $dataTable->type = 'friend';

            return $dataTable->ajax();
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    public function listBlocked(FriendshipDataTable $dataTable)
    {
        try {
            $dataTable->type = 'blocked';

            return $dataTable->ajax();
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }
}
