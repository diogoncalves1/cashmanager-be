<?php
namespace Modules\User\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\User\Http\Resources\UserResource;
use Modules\User\Repositories\UserRepository;

class UserController extends ApiController
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function updateSettings(Request $request): JsonResponse
    {
        try {
            $user = $this->repository->updateSettings($request);

            return $this->ok(new UserResource($user), __('user::messages.users.update'));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Check a listing of the usernames.
     * @param Request $request
     * @return JsonResponse
     */
    public function checkUsername(Request $request): JsonResponse
    {
        try {
            $exists = $this->repository->checkUsername($request);

            return $this->ok(['exists' => $exists]);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }
}
