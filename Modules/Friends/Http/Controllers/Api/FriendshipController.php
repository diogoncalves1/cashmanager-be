<?php

namespace Modules\Friends\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Friends\Http\Resources\FriendshipResource;
use Modules\Friends\Paginates\FriendshipPaginate;
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
     *
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
     *
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

    public function listFriends(Request $request, FriendshipPaginate $paginate): JsonResponse
    {
        return $this->safe(function () use ($request, $paginate) {
            $paginate->type = 'friend';

            return $this->respondModulePaginated($paginate, $request);
        });
    }

    public function listBlocked(Request $request, FriendshipPaginate $paginate): JsonResponse
    {
        return $this->safe(function () use ($request, $paginate) {
            $paginate->type = 'blocked';

            return $this->respondModulePaginated($paginate, $request);
        });
    }
}
