<?php
namespace Modules\Accounts\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Accounts\DataTables\AccountDataTable;
use Modules\Accounts\Http\Requests\AccountRequest;
use Modules\Accounts\Http\Resources\AccountResource;
use Modules\Accounts\Http\Resources\AccountViewResource;
use Modules\Accounts\Repositories\AccountRepository;

/**
 * Finished: True
 */
class AccountController extends ApiController
{
    private AccountRepository $repository;

    public function __construct(AccountRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @param AccountDataTable $dataTable
     * @return JsonResponse
     */
    public function index(AccountDataTable $dataTable)
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
     * @param AccountRequest $request
     * @return JsonResponse
     */
    public function store(AccountRequest $request): JsonResponse
    {
        try {
            $account = $this->repository->store($request);

            return $this->ok(new AccountResource($account), __('accounts::messages.accounts.store', ['name' => $account->name]));
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
            $account = $this->repository->showToUser($request, $id);

            return $this->ok(new AccountViewResource($account));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Update the specified resource in storage.
     * @param AccountRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(AccountRequest $request, string $id)
    {
        try {
            $account = $this->repository->update($request, $id);

            return $this->ok(new AccountResource($account), __('accounts::messages.accounts.update', ['name' => $account->name]));
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
            $account = $this->repository->destroy($request, $id);

            return $this->ok(message: __('accounts::messages.accounts.destroy', ['name' => $account->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }
}
