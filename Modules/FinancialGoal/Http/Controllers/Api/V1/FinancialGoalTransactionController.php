<?php

namespace Modules\FinancialGoal\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Modules\FinancialGoal\Http\Requests\CreateFinancialGoalTransactionRequest;
use Modules\FinancialGoal\Http\Requests\UpdateFinancialGoalTransactionRequest;
use Modules\FinancialGoal\Http\Resources\FinancialGoalTransactionResource;
use Modules\FinancialGoal\Http\Resources\FinancialGoalTransactionViewResource;
use Modules\FinancialGoal\Paginates\FinancialGoalTransactionPaginate;
use Modules\FinancialGoal\Repositories\FinancialGoalTransactionRepository;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
     *
     * @param  FinancialGoalTransactionPaginate  $dataTable
     */
    public function index(Request $request, FinancialGoalTransactionPaginate $paginate): JsonResponse
    {
        return $this->safe(function () use ($request, $paginate) {
            return $this->respondModulePaginated($paginate, $request);
        });
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateFinancialGoalTransactionRequest $request): JsonResponse
    {
        try {
            $financialGoalTransaction = $this->repository->store($request);

            return $this->ok(new FinancialGoalTransactionResource($financialGoalTransaction), __('financialgoal::messages.financial-goal-transactions.store', ['name' => $financialGoalTransaction->financialGoal->name]));
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (HttpExceptionInterface $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], $e->getStatusCode());
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        } catch (\Exception $e) {
            Log::error($e);

            return $this->fail($e->getMessage(), $e, 500);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $financialGoalTransaction = $this->repository->showToUser($request, $id);

            return $this->ok(new FinancialGoalTransactionViewResource($financialGoalTransaction));
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (HttpExceptionInterface $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], $e->getStatusCode());
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFinancialGoalTransactionRequest $request, string $id): JsonResponse
    {
        try {

            $financialGoalTransaction = $this->repository->update($request, $id);

            return $this->ok(new FinancialGoalTransactionResource($financialGoalTransaction), __('financialgoal::messages.financial-goal-transactions.update', ['name' => $financialGoalTransaction->financialGoal->name]));
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (HttpExceptionInterface $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], $e->getStatusCode());
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        } catch (\Exception $e) {
            Log::error($e);

            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $financialGoalTransaction = $this->repository->destroy($request, $id);

            return $this->ok(message: __('financialgoal::messages.financial-goal-transactions.destroy', ['name' => $financialGoalTransaction->financialGoal->name]));
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (HttpExceptionInterface $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], $e->getStatusCode());
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        } catch (\Exception $e) {
            Log::error($e);

            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Confirm the specified resource from storage.
     */
    public function confirm(Request $request, string $id): JsonResponse
    {
        try {
            $financialGoalTransaction = $this->repository->confirm($request, $id);

            return $this->ok(new FinancialGoalTransactionResource($financialGoalTransaction), __('financialgoal::messages.financial-goal-transactions.confirm', ['name' => $financialGoalTransaction->financialGoal->name]));
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (HttpExceptionInterface $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], $e->getStatusCode());
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        } catch (\Exception $e) {
            Log::error($e);

            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }
}
