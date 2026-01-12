<?php
namespace Modules\FinancialGoal\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\FinancialGoal\Http\Requests\InviteUserFinancialGoalRequest;
use Modules\FinancialGoal\Http\Resources\FinancialGoalUserInviteResource;
use Modules\FinancialGoal\Http\Resources\FinancialGoalUserResource;
use Modules\FinancialGoal\Repositories\FinancialGoalUserInviteRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * [x]
 */
class FinancialGoalUserInviteController extends ApiController
{
    private FinancialGoalUserInviteRepository $repository;

    public function __construct(FinancialGoalUserInviteRepository $financialGoalUserRepository)
    {
        $this->repository = $financialGoalUserRepository;
    }

    /**
     * Invite the user for financial goal.
     * @param InviteUserFinancialGoalRequest $request
     * @param string $id
     * @param string $userId
     * @return JsonResponse
     */
    public function invite(InviteUserFinancialGoalRequest $request, string $id, string $userId): JsonResponse
    {
        try {
            $invite = $this->repository->invite($request, $id, $userId);

            return $this->ok(new FinancialGoalUserInviteResource($invite), __('financialgoal::messages.financial-goal-user-invites.invite', ['goalName' => $invite->financialGoal->name, 'userName' => $invite->user->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }

    }

    /**
     * Accept financial goal invite.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function accept(Request $request, string $id)
    {
        try {
            $relation = $this->repository->accept($request, $id);

            return $this->ok(new FinancialGoalUserResource($relation), __('financialgoal::messages.financial-goal-user-invites.accept', ['name' => $relation->financialGoal->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Destroy financial goal invite.
     * @param Request $request
     * @param string $id
     * @param string $userId
     * @return JsonResponse
     */
    public function destroy(Request $request, string $id, string $userId)
    {
        try {
            $invite = $this->repository->destroyInvite($request, $id, $userId);

            return $this->ok(message: __('financialgoal::messages.financial-goal-user-invites.destroy', ['goalName' => $invite->financialGoal->name, 'userName' => $invite->user->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Revoke financial goal invite.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function revoke(Request $request, string $id): JsonResponse
    {
        try {
            $invite = $this->repository->revoke($request, $id);

            return $this->ok(message: __('financialgoal::messages.financial-goal-user-invites.revoke', ['goalName' => $invite->financialGoal->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }

    }
}
