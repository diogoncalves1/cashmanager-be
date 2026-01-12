<?php
namespace Modules\FinancialGoal\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\FinancialGoal\Http\Requests\InviteUserFinancialGoalRequest;
use Modules\FinancialGoal\Http\Resources\FinancialGoalUserResource;
use Modules\FinancialGoal\Repositories\FinancialGoalUserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * [x]
 */
class FinancialGoalUserController extends ApiController
{
    private FinancialGoalUserRepository $repository;

    public function __construct(FinancialGoalUserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Remove user from financial goal.
     * @param Request $request
     * @param string $id
     * @param string $userId
     * @return JsonResponse
     */
    public function revokeUser(Request $request, string $id, string $userId): JsonResponse
    {
        try {
            $relation = $this->repository->revokeUser($request, $id, $userId);

            return $this->ok(message: __('financialgoal::messages.financial-goal-users.revokeUser', ['userName' => $relation->user->name, 'goalName' => $relation->financialGoal->name]));
        } catch (Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Update user role from financial goal.
     * @param Request $request
     * @param string $id
     * @param string $userId
     * @return JsonResponse
     */
    public function updateUserRole(InviteUserFinancialGoalRequest $request, string $id, string $userId): JsonResponse
    {
        try {
            $relation = $this->repository->updateUserRole($request, $id, $userId);

            return $this->ok(new FinancialGoalUserResource($relation), __('financialgoal::messages.financial-goal-users.updateUserRole', ['goalName' => $relation->financialGoal->name, 'userName' => $relation->user->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Leave user role from financial goal.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function leave(Request $request, string $id): JsonResponse
    {
        try {
            $relation = $this->repository->leave($request, $id);

            return $this->ok(message: __('financialgoal::messages.financial-goal-users.leave', ['goalName' => $relation->financialGoal->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }
}
