<?php
namespace Modules\Debts\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\ActivityLog\DataTables\ActivityLogDataTable;
use Modules\Debts\DataTables\DebtDataTable;
use Modules\Debts\Http\Requests\DebtRequest;
use Modules\Debts\Http\Resources\DebtBasicViewCollection;
use Modules\Debts\Http\Resources\DebtResource;
use Modules\Debts\Http\Resources\DebtStatsResource;
use Modules\Debts\Http\Resources\DebtViewResource;
use Modules\Debts\Repositories\DebtRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class DebtController extends ApiController
{
    private DebtRepository $debtRepository;

    public function __construct(DebtRepository $debtRepository)
    {
        $this->debtRepository = $debtRepository;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @param DebtDataTable $dataTable
     * @return JsonResponse
     */
    public function index(Request $request, DebtDataTable $dataTable): JsonResponse
    {
        try {
            $stats = $this->debtRepository->getStats($request);

            $data = $dataTable->ajax()->getData(true);

            return response()->json(array_merge($data, ['stats' => $stats]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Return a resume of all user debts.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function allUser(Request $request): JsonResponse
    {
        try {
            $financialGoals = $this->debtRepository->allUser($request);

            return $this->ok(new DebtBasicViewCollection($financialGoals));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param DebtRequest $request
     * @return JsonResponse
     */
    public function store(DebtRequest $request): JsonResponse
    {
        try {
            $debt = $this->debtRepository->store($request);

            return $this->ok(new DebtResource($debt), __('debts::messages.debts.store', ['name' => $debt->name]));
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
            $debt = $this->debtRepository->showToUser($request, $id);

            return $this->ok(new DebtViewResource($debt));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Update the specified resource in storage.
     * @param DebtRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(DebtRequest $request, string $id)
    {
        try {
            $debt = $this->debtRepository->update($request, $id);

            return $this->ok(new DebtResource($debt), __('debts::messages.debts.update', ['name' => $debt->name]));
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
    public function destroy(Request $request, string $id)
    {
        try {
            $debt = $this->debtRepository->destroy($request, $id);

            return $this->ok(message: __('debts::messages.debts.destroy', ['name' => $debt->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage() ?? __('debts::messages.debts.errors.destroy'), $e, $e->getCode());
        }
    }

    /**
     * Mark paid the specified resource from storage.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function markPaid(Request $request, string $id)
    {
        try {
            $debt = $this->debtRepository->markPaid($request, $id);

            return $this->ok(message: __('debts::messages.debts.mark-paid', ['name' => $debt->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage() ?? __('debts::messages.debts.errors.mark-paid'), $e, $e->getCode());
        }
    }

    /**
     * Reset status the specified resource from storage.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function reset(Request $request, string $id)
    {
        try {
            $debt = $this->debtRepository->reset($request, $id);

            return $this->ok(message: __('debts::messages.debts.reset', ['name' => $debt->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage() ?? __('debts::messages.debts.errors.reset'), $e, $e->getCode());
        }
    }

    /**
     * Return a stats of all user debts.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function getStats(Request $request)
    {
        try {
            $stats = $this->debtRepository->getFormStats($request);

            return $this->ok(new DebtStatsResource($stats));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage() ?? __('debts::messages.debts.errors.complete'), $e, $e->getCode());
        }
    }

    /**
     * Return a activity of debt.
     * @param Request $request
     * @param ActivityLogDataTable $dataTable
     * @param string $id
     * @return JsonResponse
     */
    public function activity(ActivityLogDataTable $dataTable, Request $request, string $id)
    {
        try {
            $this->debtRepository->showToUser($request, $id);

            $dataTable->type = 'debt';
            $dataTable->id   = $id;

            return $dataTable->ajax();
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage() ?? __('debts::messages.debts.errors.complete'), $e, $e->getCode());

        }
    }
}
