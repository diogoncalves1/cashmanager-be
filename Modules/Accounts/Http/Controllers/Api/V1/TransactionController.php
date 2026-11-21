<?php

namespace Modules\Accounts\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Modules\Accounts\Http\Requests\TransactionRequest;
use Modules\Accounts\Http\Requests\TransactionUpdateRequest;
use Modules\Accounts\Http\Resources\TransactionResource;
use Modules\Accounts\Http\Resources\Transactions\Lists\TransactionListCollection;
use Modules\Accounts\Http\Resources\TransactionViewResource;
use Modules\Accounts\Paginates\TransactionPaginate;
use Modules\Accounts\Repositories\TransactionRepository;
use Modules\Category\Http\Resources\CategoryCollection;
use Modules\Category\Repositories\CategoryRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class TransactionController extends ApiController
{
    public function __construct(protected TransactionRepository $repository, protected CategoryRepository $categoryRepo, protected TransactionPaginate $paginate) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        return $this->safe(function () use ($request) {
            $categories = $this->categoryRepo->allUserTransactionsCategories($request);

            return $this->respondPaginated(
                $this->paginate->paginateByUser($request->user(), $request),
                TransactionListCollection::class,
                additionals: [
                    'stats' => $this->repository->getStats($request),
                    'categories' => new CategoryCollection($categories),
                ]
            );
        });
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TransactionRequest $request): JsonResponse
    {
        try {
            $transaction = $this->repository->store($request);

            return $this->ok(new TransactionResource($transaction), __('accounts::messages.transactions.store', ['name' => $transaction->account->name]));
        } catch (ValidationException $e) {
            throw $e;
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
            $transaction = $this->repository->showToUser($request, $id);

            return $this->ok(new TransactionViewResource($transaction));
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error($e);

            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TransactionUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $transaction = $this->repository->update($request, $id);

            return $this->ok(new TransactionResource($transaction), __('accounts::messages.transactions.update'));
        } catch (ValidationException $e) {
            throw $e;
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
            $transaction = $this->repository->destroy($request, $id);

            return $this->ok(message: __('accounts::messages.transactions.destroy'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error($e);

            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Confirm the specified resource.
     */
    public function confirm(Request $request, string $id): JsonResponse
    {
        try {
            $transaction = $this->repository->confirm($request, $id);

            return $this->ok(new TransactionResource($transaction), __('accounts::messages.transactions.confirm'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error($e);

            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }
}
