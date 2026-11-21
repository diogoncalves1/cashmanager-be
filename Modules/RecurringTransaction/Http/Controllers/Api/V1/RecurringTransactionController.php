<?php

namespace Modules\RecurringTransaction\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\Category\Http\Resources\CategoryCollection;
use Modules\Category\Repositories\CategoryRepository;
use Modules\RecurringTransaction\Http\Requests\StoreRecurringTransactionRequest;
use Modules\RecurringTransaction\Http\Requests\UpdateRecurringTransactionRequest;
use Modules\RecurringTransaction\Http\Resources\RecurringTransactionResource;
use Modules\RecurringTransaction\Paginates\RecurringTransactionPaginate;
use Modules\RecurringTransaction\Repositories\RecurringSummaryRepository;
use Modules\RecurringTransaction\Repositories\RecurringTransactionRepository;

class RecurringTransactionController extends ApiController
{
    public function __construct(
        protected RecurringTransactionRepository $repository,
        protected CategoryRepository $categoryRepo,
        protected RecurringSummaryRepository $summary,
    ) {}

    /**
     * Retrieve all recurring expenses for the given account
     *
     * @param  RecurringTransactionPaginate  $dataTable
     */
    public function index(Request $request, RecurringTransactionPaginate $paginate): JsonResponse
    {
        return $this->safe(function () use ($request, $paginate) {
            $meta = ['categories' => new CategoryCollection($this->categoryRepo->allUserRecurringsCategories($request))];
            if ($request->boolean('includeSummary')) {
                $meta['summary'] = $this->summary->forUser($request->user()->id);
            }

            return $this->respondModulePaginated($paginate, $request, $meta);
        });
    }

    /**
     * Create a new recurring expense for the given account
     */
    public function store(StoreRecurringTransactionRequest $request): JsonResponse
    {
        return $this->safe(function () use ($request) {
            $recurring = $this->repository->store($request);

            return $this->ok(new RecurringTransactionResource($recurring));
        });
    }

    public function show(string $id): JsonResponse
    {
        return $this->safe(function () use ($id) {
            $recurring = $this->repository->show($id);
            Gate::authorize('show', $recurring);

            return $this->ok(new RecurringTransactionResource($recurring->load(['account.currency', 'category', 'linked'])));
        });
    }

    /**
     * Update an existing recurring expense
     */
    public function update(UpdateRecurringTransactionRequest $request, string $id): JsonResponse
    {
        return $this->safe(function () use ($request, $id) {
            $recurring = $this->repository->update($request, $id);

            return $this->ok(new RecurringTransactionResource($recurring));
        });
    }

    /**
     * Enable or disable a recurring expense
     */
    public function toggle(string $id): JsonResponse
    {
        return $this->safe(function () use ($id) {
            $recurring = $this->repository->toggle($id);

            return $this->ok(new RecurringTransactionResource($recurring));
        });
    }

    /**
     * Soft delete a recurring expense
     */
    public function destroy(string $id): JsonResponse
    {
        return $this->safe(function () use ($id) {
            $recurring = $this->repository->destroy($id);

            return $this->ok(new RecurringTransactionResource($recurring));
        });
    }
}
