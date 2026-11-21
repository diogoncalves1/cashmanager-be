<?php

namespace Modules\FinancialGoal\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Modules\ActivityLog\Paginates\ActivityLogPaginate;
use Modules\FinancialGoal\Http\Requests\FinancialGoalRequest;
use Modules\FinancialGoal\Http\Resources\Charts\FinancialGoalMonthlyResumeCollection;
use Modules\FinancialGoal\Http\Resources\FinancialGoalBasicViewCollection;
use Modules\FinancialGoal\Http\Resources\FinancialGoalResource;
use Modules\FinancialGoal\Http\Resources\FinancialGoals\FinancialGoalStatsResource;
use Modules\FinancialGoal\Http\Resources\FinancialGoalTransactionViewCollection;
use Modules\FinancialGoal\Http\Resources\FinancialGoalViewResource;
use Modules\FinancialGoal\Paginates\FinancialGoalPaginate;
use Modules\FinancialGoal\Repositories\FinancialGoalRepository;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class FinancialGoalController extends ApiController
{
    protected FinancialGoalRepository $repository;

    public function __construct(FinancialGoalRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  FinancialGoalPaginate  $dataTable
     */
    public function index(Request $request, FinancialGoalPaginate $paginate): JsonResponse
    {
        return $this->safe(function () use ($request, $paginate) {
            return $this->respondModulePaginated($paginate, $request, ['stats' => $this->repository->getStats($request)]);
        });
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FinancialGoalRequest $request): JsonResponse
    {
        try {
            $financialGoal = $this->repository->store($request);

            return $this->ok(new FinancialGoalResource($financialGoal), __('financialgoal::messages.financial-goals.store', ['name' => $financialGoal->name]));
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
     * Show the specified resource.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $financialGoal = $this->repository->showToUser($request, $id);
            $lastTransactions = $this->repository->showLastTransactions($id);
            $monthlySummary = $this->repository->showMonthlyResume($id);

            return $this->ok(new FinancialGoalViewResource($financialGoal), additionals: [
                'transactions' => new FinancialGoalTransactionViewCollection($lastTransactions),
                'monthlySummary' => new FinancialGoalMonthlyResumeCollection($monthlySummary),
            ]);
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
     * Update the specified resource in storage.
     */
    public function update(FinancialGoalRequest $request, string $id): JsonResponse
    {
        try {
            $financialGoal = $this->repository->update($request, $id);

            return $this->ok(new FinancialGoalResource($financialGoal), __('financialgoal::messages.financial-goals.update', ['name' => $financialGoal->name]));
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
            $financialGoal = $this->repository->destroy($request, $id);

            return $this->ok(message: __('financialgoal::messages.financial-goals.destroy', ['name' => $financialGoal->name]));
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
     * Set status canceled the specified resource from storage.
     */
    public function cancel(Request $request, string $id): JsonResponse
    {
        try {
            $financialGoal = $this->repository->cancel($request, $id);

            return $this->ok(new FinancialGoalResource($financialGoal), __('financialgoal::messages.financial-goals.cancel', ['name' => $financialGoal->name]));
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
     * Set status complete the specified resource from storage.
     */
    public function complete(Request $request, string $id): JsonResponse
    {
        try {
            $financialGoal = $this->repository->complete($request, $id);

            return $this->ok(new FinancialGoalResource($financialGoal), __('financialgoal::messages.financial-goals.complete', ['name' => $financialGoal->name]));
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
     * Set status in progress the specified resource from storage.
     */
    public function reset(Request $request, string $id): JsonResponse
    {
        try {
            $financialGoal = $this->repository->reset($request, $id);

            return $this->ok(new FinancialGoalResource($financialGoal), __('financialgoal::messages.financial-goals.reset', ['name' => $financialGoal->name]));
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
     * Return a resume of all user financial goals.
     *
     * @param  string  $id
     */
    public function allUser(Request $request): JsonResponse
    {
        try {
            $financialGoals = $this->repository->allUser($request);

            return $this->ok(new FinancialGoalBasicViewCollection($financialGoals));
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
     * Return a stats of all user financial goals.
     *
     * @param  string  $id
     */
    public function getStats(Request $request): JsonResponse
    {
        try {
            $stats = $this->repository->getFormStats($request);

            return $this->ok(new FinancialGoalStatsResource($stats));
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
     * Return a activity of financial goal.
     *
     * @param  ActivityLogPaginate  $dataTable
     */
    public function activity(Request $request, ActivityLogPaginate $paginate, string $id): JsonResponse
    {
        return $this->safe(function () use ($request, $paginate, $id) {
            $this->repository->showToUser($request, $id);
            $paginate->type = 'financial_goal';
            $paginate->id = $id;

            return $this->respondModulePaginated($paginate, $request);
        });
    }
}
