<?php

namespace Modules\Currency\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Modules\Currency\Http\Requests\CheckCurrencyCodeRequest;
use Modules\Currency\Repositories\CurrencyRepository;
use Modules\Currency\Http\Requests\CurrencyRequest;
use Modules\Currency\Http\Resources\CurrencyCollection;
use Modules\Currency\Http\Resources\CurrencyResource;

class CurrencyController extends ApiController
{
    private CurrencyRepository $repository;

    public function __construct(CurrencyRepository $currencyRepository)
    {
        $this->repository = $currencyRepository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function index()
    {
        return $this->ok(new CurrencyCollection($this->repository->all()));
    }

    /**
     * Store a newly created resource in storage.
     * @param CurrencyRequest $request
     * @return JsonResponse
     */
    public function store(CurrencyRequest $request): JsonResponse
    {
        try {
            $this->allowedAction('createCurrency');

            $currency = $this->repository->store($request);

            return $this->ok(new CurrencyResource($currency), "Moeda adicionada com sucesso!");
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail('Erro ao tentar adicionar uma nova moeda', $e, $e->getCode());
        }
    }

    /**
     * Show the specified resource.
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        try {
            $currency = $this->repository->show($id);

            return $this->ok(new CurrencyResource($currency));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail('Erro ao tentar buscar moeda.', $e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Update the specified resource in storage.
     * @param CurrencyRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(CurrencyRequest $request, string $id): JsonResponse
    {
        try {
            $this->allowedAction('editCurrency');

            $currency = $this->repository->update($request, $id);

            return $this->ok(new CurrencyResource($currency), "Moeda atualizada com sucesso!");
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail('Erro ao tentar atualizar moeda.', $e, $e->getCode());
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->allowedAction('destroyCurrency');

            $currency = $this->repository->destroy($id);

            return $this->ok(new CurrencyResource($currency), 'Moeda apagada com sucesso!');
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail('Erro ao tentar atualizar moeda.', $e, $e->getCode());
        }
    }

    /**
     * Check the specified resource.
     * @param CheckCurrencyCodeRequest $request
     * @return JsonResponse
     */
    public function checkCode(CheckCurrencyCodeRequest $request): JsonResponse
    {
        try {
            $this->allowedAction('viewCurrency');

            $exists = $this->repository->checkCode($request);

            return $this->ok(additionals: ['exists' => $exists]);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail('Erro ao tentar verificar código da moeda.', $e, $e->getCode());
        }
    }

    /**
     * Update the specified resource in storage.
     * @return JsonResponse
     */
    public function updateRates(): JsonResponse
    {
        try {
            $this->allowedAction('updateRates');

            Artisan::call('currency:fetch-daily');

            return $this->ok(message: 'Taxas atualizadas com sucesso');
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail('Erro ao tentar atualizar taxas das moedas.', $e, $e->getCode());
        }
    }
}
