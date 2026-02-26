<?php
namespace Modules\Accounts\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Accounts\DataTables\TransactionDataTable;
use Modules\Accounts\Http\Requests\TransactionRequest;
use Modules\Accounts\Http\Requests\TransactionUpdateRequest;
use Modules\Accounts\Http\Resources\TransactionResource;
use Modules\Accounts\Http\Resources\TransactionViewResource;
use Modules\Accounts\Repositories\AccountRepository;
use Modules\Accounts\Repositories\TransactionRepository;
use Modules\Category\Http\Resources\CategoryCollection;
use Modules\Category\Repositories\CategoryRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Done: false
 * TODO: Test All methods
 * Dones: index, store, show, update, destroy
 */
class TransactionController extends ApiController
{
    private TransactionRepository $repository;

    public function __construct(TransactionRepository $repository, protected AccountRepository $accountRepo, protected CategoryRepository $categoryRepo)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @param AccountDataTable $dataTable
     * @return JsonResponse
     */
    public function index(Request $request, TransactionDataTable $dataTable): JsonResponse
    {
        try {
            $stats = $this->repository->getStats($request);

            $data       = $dataTable->ajax()->getData(true);
            $categories = $this->categoryRepo->allUserTransactionsCategories($request);

            return response()->json(array_merge(['data' => $data['data'], 'recordsTotal' => $data['recordsTotal'], 'recordsFiltered' => $data['recordsFiltered']], ['stats' => $stats, 'categories' => new CategoryCollection($categories)]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param TransactionRequest $request
     * @return JsonResponse
     */
    public function store(TransactionRequest $request): JsonResponse
    {
        try {
            $transaction = $this->repository->store($request);

            return $this->ok(new TransactionResource($transaction), __('accounts::messages.transactions.store', ['name' => $transaction->account->name]));
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
            $transaction = $this->repository->showToUser($request, $id);

            return $this->ok(new TransactionViewResource($transaction));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Update the specified resource in storage.
     * @param TransactionUpdateRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(TransactionUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $transaction = $this->repository->update($request, $id);

            return $this->ok(new TransactionResource($transaction), __('accounts::messages.transactions.update'));
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
            $transaction = $this->repository->destroy($request, $id);

            return $this->ok(message: __('accounts::messages.transactions.destroy'));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Confirm the specified resource.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function confirm(Request $request, string $id): JsonResponse
    {
        try {
            $transaction = $this->repository->confirm($request, $id);

            return $this->ok(new TransactionResource($transaction), __('accounts::messages.transactions.confirm'));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }
}
