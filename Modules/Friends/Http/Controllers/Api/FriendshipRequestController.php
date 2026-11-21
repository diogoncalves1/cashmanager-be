<?php

namespace Modules\Friends\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Friends\Http\Resources\FriendshipRequestResource;
use Modules\Friends\Http\Resources\FriendshipResource;
use Modules\Friends\Http\Resources\FriendshipStatsResource;
use Modules\Friends\Paginates\FriendshipRequestPaginate;
use Modules\Friends\Repositories\FriendshipRequestRepository;

class FriendshipRequestController extends ApiController
{
    private FriendshipRequestRepository $repository;

    public function __construct(FriendshipRequestRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Send a friend request.
     */
    public function send(Request $request, string $id): JsonResponse
    {
        try {
            $friendshipRequest = $this->repository->send($request, $id);

            return $this->ok(new FriendshipRequestResource($friendshipRequest), __('friends::messages.friendship-requests.send', ['name' => $friendshipRequest->receiver->name]));
        } catch (\Exception $e) {
            Log::error($e);

            return $this->fail($e->getMessage() ?? __('friends::messages.friendship-requests.errors.send'), $e, $e->getCode());
        }
    }

    /**
     * Accept a friend request.
     *
     * @return JsonResponse
     */
    public function accept(Request $request, string $id)
    {
        try {
            $friendship = $this->repository->accept($request, $id);

            return $this->ok(new FriendshipResource($friendship), __('friends::messages.friendship-requests.accept', ['name' => $friendship->sender->name]));
        } catch (\Exception $e) {
            Log::error($e);

            return $this->fail($e->getMessage() ?? __('friends::messages.friendship-requests.errors.accept'), $e, $e->getCode());
        }
    }

    /**
     * Decline a friend request.
     *
     * @return JsonResponse
     */
    public function decline(Request $request, string $id)
    {
        try {
            $friendshipRequest = $this->repository->decline($request, $id);

            return $this->ok(new FriendshipResource($friendshipRequest), __('friends::messages.friendship-requests.decline', ['name' => $friendshipRequest->sender->name]));
        } catch (\Exception $e) {
            Log::error($e);

            return $this->fail($e->getMessage() ?? __('friends::messages.friendship-requests.errors.decline'), $e, $e->getCode());
        }
    }

    /**
     * Cancel a friend request.
     *
     * @return JsonResponse
     */
    public function cancel(Request $request, string $id)
    {
        try {
            $friendshipRequest = $this->repository->cancel($request, $id);

            return $this->ok(new FriendshipResource($friendshipRequest), __('friends::messages.friendship-requests.cancel', ['name' => $friendshipRequest->receiver->name]));
        } catch (\Exception $e) {
            Log::error($e);

            return $this->fail($e->getMessage() ?? __('friends::messages.friendship-requests.errors.cancel'), $e, $e->getCode());
        }
    }

    /**
     * List sent friend requests.
     *
     * @param  FriendshipRequestPaginate  $request
     */
    public function listSent(Request $request, FriendshipRequestPaginate $paginate): JsonResponse
    {
        return $this->safe(function () use ($request, $paginate) {
            $paginate->type = 'sent';

            return $this->respondModulePaginated($paginate, $request);
        });
    }

    /**
     * Cancel a friend request.
     *
     * @param  string  $id
     */
    public function listReceived(Request $request, FriendshipRequestPaginate $paginate): JsonResponse
    {
        return $this->safe(function () use ($request, $paginate) {
            $paginate->type = 'received';

            return $this->respondModulePaginated($paginate, $request);
        });
    }

    /**
     * Friendship stats.
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $stats = $this->repository->stats($request);

            return $this->ok(new FriendshipStatsResource($stats));
        } catch (\Exception $e) {
            Log::error($e);

            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }
}
