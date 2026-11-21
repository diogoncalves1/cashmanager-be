<?php

namespace Modules\Debts\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Debts\Http\Requests\DebtPaymentRequest;
use Modules\Debts\Http\Requests\DebtPaymentUpdateRequest;
use Modules\Debts\Http\Resources\DebtPaymentResource;
use Modules\Debts\Http\Resources\DebtPayments\DebtPaymentViewResource;
use Modules\Debts\Paginates\DebtPaymentPaginate;
use Modules\Debts\Repositories\DebtPaymentRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class DebtPaymentController extends ApiController
{
    private DebtPaymentRepository $repository;

    public function __construct(DebtPaymentRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request, DebtPaymentPaginate $paginate): \Illuminate\Http\JsonResponse
    {
        return $this->safe(function () use ($request, $paginate) {
            return $this->respondModulePaginated($paginate, $request);
        });
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DebtPaymentRequest $request): JsonResponse
    {
        try {
            $debtPayment = $this->repository->store($request);

            return $this->ok(new DebtPaymentResource($debtPayment), __('debts::messages.debt-payments.store'));
        } catch (\Exception $e) {
            Log::error($e);

            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Show the specified resource.
     *
     * @return JsonResponse
     */
    public function show(Request $request, string $id)
    {
        try {
            $debtPayment = $this->repository->showToUser($request, $id);

            return $this->ok(new DebtPaymentViewResource($debtPayment));
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
    public function update(DebtPaymentUpdateRequest $request, string $id)
    {
        try {
            $debtPayment = $this->repository->update($request, $id);

            return $this->ok(new DebtPaymentResource($debtPayment), __('debts::messages.debt-payments.update'));
        } catch (\Exception $e) {
            Log::error($e);

            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return JsonResponse
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $this->repository->destroy($request, $id);

            return $this->ok(message: __('debts::messages.debt-payments.destroy'));
        } catch (\Exception $e) {
            Log::error($e);

            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Confirm the specified resource.
     *
     * @return JsonResponse
     */
    public function confirm(Request $request, string $id)
    {
        try {
            $debtPayment = $this->repository->confirm($request, $id);

            return $this->ok(new DebtPaymentResource($debtPayment), __('debts::messages.debt-payments.confirm'));
        } catch (\Exception $e) {
            Log::error($e);

            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }
}
