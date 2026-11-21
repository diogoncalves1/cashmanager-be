<?php

namespace Modules\Accounts\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Accounts\Http\Requests\AccountRequest;
use Modules\Accounts\Http\Resources\Accounts\AccountBasicCollection;
use Modules\Accounts\Http\Resources\Accounts\AccountResource;
use Modules\Accounts\Http\Resources\Accounts\AccountViewResource;
use Modules\Accounts\Http\Resources\Accounts\Lists\AccountListCollection;
use Modules\Accounts\Http\Resources\CategorySummaryCollection;
use Modules\Accounts\Http\Resources\Charts\BalanceChartsResource;
use Modules\Accounts\Http\Resources\MonthlyResumeCollection;
use Modules\Accounts\Http\Resources\Stats\AccountIndividualDataResource;
use Modules\Accounts\Paginates\AccountPaginate;
use Modules\Accounts\Repositories\AccountRepository;
use Modules\ActivityLog\Paginates\ActivityLogPaginate;

class AccountController extends ApiController
{
    public function __construct(protected AccountRepository $repository, protected AccountPaginate $paginate) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        return $this->safe(function () use ($request) {
            return $this->respondPaginated(
                $this->paginate->paginateByUser($request->user(), $request),
                AccountListCollection::class,
                additionals: [
                    'stats' => $this->repository->getStats($request),
                ]
            );
        });
    }

    /**
     * Store a newly created resource in storage.
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
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $account = $this->repository->showToUser($request, $id);

            $monthlyResume = $this->repository->showMonthlyResume($id);
            $categorySummary = $this->repository->showCategorySummary($account);
            $balanceCharts = $this->repository->getChartsData($request, $id);
            $extraData = $this->repository->getAccountIndividualData($account);
            $extraData = array_merge($extraData, ['balance' => $account->balance, 'balanceLastMonth' => isset($balanceCharts['charts']['monthly'][0]) ? $balanceCharts['charts']['monthly'][0]->amount ?? 0 : 0]);

            return $this->ok(new AccountViewResource($account), additionals: [
                'monthlyResume' => new MonthlyResumeCollection($monthlyResume),
                'extraData' => new AccountIndividualDataResource($extraData),
                'balanceChart' => new BalanceChartsResource($balanceCharts),
                'categorySummary' => [
                    'data' => new CategorySummaryCollection($categorySummary['data']),
                    'total' => $categorySummary['total'],
                    'totalFormated' => $categorySummary['totalFormated'],
                ]]);
        } catch (\Exception $e) {
            Log::error($e);

            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Update the specified resource in storage.
     *
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

    /**
     * Update status the specified resource from storage.
     */
    public function status(Request $request, string $id): JsonResponse
    {
        try {
            $account = $this->repository->status($request, $id);

            return $this->ok(new AccountResource($account), __('accounts::messages.accounts.status', ['name' => $account->name, 'status' => __('accounts::attributes.accounts.status.'.($account->active ? 'activated' : 'inactivated'))]));
        } catch (\Exception $e) {
            Log::error($e);

            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function allUser(Request $request): JsonResponse
    {
        return $this->safe(function () use ($request) {
            $accounts = $this->repository->getAccountsByUserPermission($request->user(), 'createTransaction');

            return $this->ok(new AccountBasicCollection($accounts));
        });
    }

    /**
     * Return a activity of account.
     *
     * @param  ActivityLogPaginate  $dataTable
     */
    public function activity(Request $request, ActivityLogPaginate $paginate, string $id): JsonResponse
    {
        return $this->safe(function () use ($request, $paginate, $id) {
            $this->repository->showToUser($request, $id);
            $paginate->type = 'account';
            $paginate->id = $id;

            return $this->respondModulePaginated($paginate, $request);
        });
    }
}
