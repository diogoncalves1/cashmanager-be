<?php
namespace Modules\Asset\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Asset\Http\Resources\AssetResource;
use Modules\Asset\Http\Resources\AssetSearchViewColelction;
use Modules\Asset\Repositories\AssetRepository;

class AssetController extends ApiController
{
    protected AssetRepository $repository;

    public function __construct(AssetRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show the specified resource.
     */
    public function show(string $id)
    {
        try {
            $asset = $this->repository->show($id);

            return $this->ok(new AssetResource($asset));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail('Erro ao carregar asset.', $e);
        }
    }

    /**
     * Show the specified resource by ticker.
     * @param string $ticker
     * @return JsonResponse
     */
    public function showByTicker(string $ticker): JsonResponse
    {
        try {
            $asset = $this->repository->showByTicker($ticker);

            return $this->ok(new AssetResource($asset));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail('Erro ao pegar asset com ticker: ' . $ticker, $e);
        }
    }

    public function search(Request $request)
    {
        try {
            $assets = $this->repository->search($request);

            return $this->ok(new AssetSearchViewColelction($assets));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail('Erro ao carregar asset.', $e);
        }
    }
}
