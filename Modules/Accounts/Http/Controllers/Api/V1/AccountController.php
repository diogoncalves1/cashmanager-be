<?php
namespace Modules\Accounts\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Accounts\DataTables\AccountDataTable;
use Modules\Accounts\Http\Requests\AccountRequest;
use Modules\Accounts\Http\Resources\AccountBasicViewCollection;
use Modules\Accounts\Http\Resources\AccountResource;
use Modules\Accounts\Http\Resources\AccountViewResource;
use Modules\Accounts\Http\Resources\CategorySummaryCollection;
use Modules\Accounts\Http\Resources\Charts\BalanceChartsResource;
use Modules\Accounts\Http\Resources\MonthlyResumeCollection;
use Modules\Accounts\Http\Resources\Stats\AccountIndividualDataResource;
use Modules\Accounts\Repositories\AccountRepository;
use Modules\ActivityLog\DataTables\ActivityLogDataTable;

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
     * @param Request $request
     * @param AccountDataTable $dataTable
     * @return JsonResponse
     */
    public function index(Request $request, AccountDataTable $dataTable): JsonResponse
    {
        try {
            $stats = $this->repository->getStats($request);

            $data = $dataTable->ajax()->getData(true);

            return response()->json(array_merge($data, ['stats' => $stats]));
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

            $monthlyResume   = $this->repository->showMonthlyResume($id);
            $categorySummary = $this->repository->showCategorySummary($account);
            $balanceCharts   = $this->repository->getChartsData($request, $id);
            $extraData       = $this->repository->getAccountIndividualData($account);
            $extraData       = array_merge($extraData, ['balance' => $account->balance, 'balanceLastMonth' => isset($balanceCharts['charts']['monthly'][0]) ? $balanceCharts['charts']['monthly'][0]->amount ?? 0 : 0]);

            return $this->ok(new AccountViewResource($account), additionals: [
                'monthlyResume'   => new MonthlyResumeCollection($monthlyResume),
                'extraData'       => new AccountIndividualDataResource($extraData),
                'balanceChart'    => new BalanceChartsResource($balanceCharts),
                'categorySummary' =>
                [
                    'data'          => new CategorySummaryCollection($categorySummary['data']),
                    'total'         => $categorySummary['total'],
                    'totalFormated' => $categorySummary['totalFormated'],
                ]]);
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

    /**
     * Update status the specified resource from storage.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function status(Request $request, string $id): JsonResponse
    {
        try {
            $account = $this->repository->status($request, $id);

            return $this->ok(new AccountResource($account), __('accounts::messages.accounts.status', ['name' => $account->name, 'status' => __('accounts::attributes.accounts.status.' . ($account->active ? 'activated' : 'inactivated'))]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function allUser(Request $request): JsonResponse
    {
        try {
            $accounts = $this->repository->allUser($request);

            return $this->ok(new AccountBasicViewCollection($accounts));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Return a activity of account.
     * @param ActivityLogDataTable $dataTable
     * @param Request $request
     * @param string $id
     * @param string $id
     * @return JsonResponse
     */
    public function activity(ActivityLogDataTable $dataTable, Request $request, string $id): JsonResponse
    {
        try {
            $this->repository->showToUser($request, $id);

            $dataTable->type = 'account';
            $dataTable->id   = $id;

            return $dataTable->ajax();
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

}
