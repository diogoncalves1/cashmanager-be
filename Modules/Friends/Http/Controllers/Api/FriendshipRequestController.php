<?php
namespace Modules\Friends\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Friends\DataTables\FriendshipRequestDataTable;
use Modules\Friends\Http\Resources\FriendshipRequestResource;
use Modules\Friends\Http\Resources\FriendshipResource;
use Modules\Friends\Http\Resources\FriendshipStatsResource;
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
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function send(Request $request, string $id): JsonResponse
    {
        try {
            $friendshipRequest = $this->repository->send($request, $id);

            return $this->ok(new FriendshipRequestResource($friendshipRequest), __('friends::messages.friendship-requests.send', ['name' => $friendshipRequest->receiver->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage() ?? __('friends::messages.friend-requests.errors.send'), $e, $e->getCode());
        }
    }

    /**
     * Accept a friend request.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function accept(Request $request, string $id)
    {
        try {
            $friendship = $this->repository->accept($request, $id);

            return $this->ok(new FriendshipResource($friendship), __('friends::messages.friendship-requests.accept', ['name' => $friendship->sender->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage() ?? __('friends::messages.friend-requests.errors.accept'), $e, $e->getCode());
        }
    }

    /**
     * Decline a friend request.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function decline(Request $request, string $id)
    {
        try {
            $friendshipRequest = $this->repository->decline($request, $id);

            return $this->ok(new FriendshipResource($friendshipRequest), __('friends::messages.friendship-requests.decline', ['name' => $friendshipRequest->sender->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage() ?? __('friends::messages.friend-requests.errors.decline'), $e, $e->getCode());
        }
    }

    /**
     * Cancel a friend request.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function cancel(Request $request, string $id)
    {
        try {
            $friendshipRequest = $this->repository->cancel($request, $id);

            return $this->ok(new FriendshipResource($friendshipRequest), __('friends::messages.friendship-requests.cancel', ['name' => $friendshipRequest->receiver->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage() ?? __('friends::messages.friend-requests.errors.cancel'), $e, $e->getCode());
        }
    }

    /**
     * List sent friend requests.
     * @param FriendshipRequestDataTable $request
     * @return JsonResponse
     */
    public function listSent(FriendshipRequestDataTable $dataTable)
    {
        try {
            $dataTable->type   = 'sent';
            $dataTable->status = 'pending';

            return $dataTable->ajax();
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Cancel a friend request.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function listReceived(FriendshipRequestDataTable $dataTable)
    {
        try {
            $dataTable->type   = 'received';
            $dataTable->status = 'pending';

            return $dataTable->ajax();
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Friendship stats.
     * @param Request $request
     * @return JsonResponse
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
