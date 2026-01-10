<?php
namespace Modules\FinancialGoal\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\FinancialGoal\DataTables\FinancialGoalTransactionDataTable;
use Modules\FinancialGoal\Http\Requests\CreateFinancialGoalTransactionRequest;
use Modules\FinancialGoal\Http\Requests\UpdateFinancialGoalTransactionRequest;
use Modules\FinancialGoal\Http\Resources\FinancialGoalTransactionResource;
use Modules\FinancialGoal\Http\Resources\FinancialGoalTransactionViewResource;
use Modules\FinancialGoal\Repositories\FinancialGoalTransactionRepository;

/**
 * [x]
 */
class FinancialGoalTransactionController extends ApiController
{
    protected FinancialGoalTransactionRepository $repository;

    public function __construct(FinancialGoalTransactionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @param FinancialGoalTransactionDataTable $dataTable
     * @return JsonResponse
     */
    public function index(FinancialGoalTransactionDataTable $dataTable): JsonResponse
    {
        try {
            return $dataTable->ajax();
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param CreateFinancialGoalTransactionRequest $request
     * @return JsonResponse
     */
    public function store(CreateFinancialGoalTransactionRequest $request): JsonResponse
    {
        try {
            $financialGoalTransaction = $this->repository->store($request);

            return $this->ok(new FinancialGoalTransactionResource($financialGoalTransaction), __('financialgoal::messages.financial-goal-transactions.store', ['name' => $financialGoalTransaction->financialGoal->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, 500);
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
            $financialGoalTransaction = $this->repository->showToUser($request, $id);

            return $this->ok(new FinancialGoalTransactionViewResource($financialGoalTransaction));
        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Update the specified resource in storage.
     * @param UpdateFinancialGoalTransactionRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(UpdateFinancialGoalTransactionRequest $request, string $id): JsonResponse
    {
        try {

            $financialGoalTransaction = $this->repository->update($request, $id);

            return $this->ok(new FinancialGoalTransactionResource($financialGoalTransaction), __('financialgoal::messages.financial-goals-transactions.update', ['name' => $financialGoalTransaction->financialGoal->name]));
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
            $financialGoalTransaction = $this->repository->destroy($request, $id);

            return $this->ok(message: __('financialgoal::messages.financial-goal-transactions.destroy', ['name' => $financialGoalTransaction->financialGoal->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Confirm the specified resource from storage.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function confirm(Request $request, string $id): JsonResponse
    {
        try {
            $financialGoalTransaction = $this->repository->confirm($request, $id);

            return $this->ok(new FinancialGoalTransactionResource($financialGoalTransaction), __('financialgoal::messages.financial-goal-transactions.confirm', ['name' => $financialGoalTransaction->financialGoal->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }
}
