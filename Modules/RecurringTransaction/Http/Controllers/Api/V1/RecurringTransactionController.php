<?php
namespace Modules\RecurringTransaction\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Http\Resources\DataTableResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\Category\Http\Resources\CategoryCollection;
use Modules\Category\Repositories\CategoryRepository;
use Modules\RecurringTransaction\DataTables\RecurringTransactionDataTable;
use Modules\RecurringTransaction\Http\Requests\StoreRecurringTransactionRequest;
use Modules\RecurringTransaction\Http\Requests\UpdateRecurringTransactionRequest;
use Modules\RecurringTransaction\Http\Resources\RecurringTransactionResource;
use Modules\RecurringTransaction\Repositories\RecurringTransactionRepository;
use Modules\RecurringTransaction\Repositories\RecurringSummaryRepository;

class RecurringTransactionController extends ApiController
{
    public function __construct(
        protected RecurringTransactionRepository $repository,
        protected CategoryRepository $categoryRepo,
        protected RecurringSummaryRepository $summary,
    ) {}

    /**
     * Retrieve all recurring expenses for the given account
     *  @param RecurringTransactionDataTable $dataTable
     *  @param Request $request
     */
    public function index(RecurringTransactionDataTable $dataTable, Request $request)
    {
        return $this->safe(function () use ($dataTable, $request) {
            $data = $dataTable->ajax()->getData(true);

            $categories = $this->categoryRepo->allUserRecurringsCategories($request);

            $data['meta'] = [
                'categories' => new CategoryCollection($categories),
            ];

            if ($request->boolean('includeSummary')) {
                $data['meta']['summary'] = $this->summary->forUser($request->user()->id);
            }

            return new DataTableResource($data);
        });
    }

    /**
     * Create a new recurring expense for the given account
     *  @param StoreRecurringTransactionRequest $request
     *  @return JsonResponse
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
     *  @param UpdateRecurringTransactionRequest $request
     *  @param string $id
     *  @return JsonResponse
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
     *  @param string $id
     *  @return JsonResponse
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
     *  @param string $id
     *  @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        return $this->safe(function () use ($id) {
            $recurring = $this->repository->destroy($id);

            return $this->ok(new RecurringTransactionResource($recurring));
        });
    }
}
