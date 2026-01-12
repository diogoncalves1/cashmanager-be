<?php
namespace Modules\FinancialGoal\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\FinancialGoal\DataTables\FinancialGoalDataTable;
use Modules\FinancialGoal\Http\Requests\FinancialGoalRequest;
use Modules\FinancialGoal\Http\Resources\FinancialGoalResource;
use Modules\FinancialGoal\Http\Resources\FinancialGoalViewResource;
use Modules\FinancialGoal\Repositories\FinancialGoalRepository;

/**
 * [x]
 */
class FinancialGoalController extends ApiController
{
    protected FinancialGoalRepository $repository;

    public function __construct(FinancialGoalRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @param FinancialGoalDataTable $dataTable
     * @return JsonResponse
     */
    public function index(FinancialGoalDataTable $dataTable): JsonResponse
    {
        try {
            return $dataTable->ajax();
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param FinancialGoalRequest $request
     * @return JsonResponse
     */
    public function store(FinancialGoalRequest $request): JsonResponse
    {
        try {
            $financialGoal = $this->repository->store($request);

            return $this->ok(new FinancialGoalResource($financialGoal), __('financialgoal::messages.financial-goals.store', ['name' => $financialGoal->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Show the specified resource.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $financialGoal = $this->repository->showToUser($request, $id);

            return $this->ok(new FinancialGoalViewResource($financialGoal));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Update the specified resource in storage.
     * @param FinancialGoalRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(FinancialGoalRequest $request, string $id): JsonResponse
    {
        try {
            $financialGoal = $this->repository->update($request, $id);

            return $this->ok(new FinancialGoalResource($financialGoal), __('financialgoal::messages.financial-goals.update', ['name' => $financialGoal->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $financialGoal = $this->repository->destroy($request, $id);

            return $this->ok(message: __('financialgoal::messages.financial-goals.destroy', ['name' => $financialGoal->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Set status canceled the specified resource from storage.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function cancel(Request $request, string $id): JsonResponse
    {
        try {
            $financialGoal = $this->repository->cancel($request, $id);

            return $this->ok(new FinancialGoalResource($financialGoal), __('financialgoal::messages.financial-goals.cancel', ['name' => $financialGoal->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Set status complete the specified resource from storage.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function complete(Request $request, string $id): JsonResponse
    {
        try {
            $financialGoal = $this->repository->complete($request, $id);

            return $this->ok(new FinancialGoalResource($financialGoal), __('financialgoal::messages.financial-goals.complete', ['name' => $financialGoal->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Set status in progress the specified resource from storage.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function reset(Request $request, string $id): JsonResponse
    {
        try {
            $financialGoal = $this->repository->reset($request, $id);

            return $this->ok(new FinancialGoalResource($financialGoal), __('financialgoal::messages.financial-goals.reset', ['name' => $financialGoal->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }
}
