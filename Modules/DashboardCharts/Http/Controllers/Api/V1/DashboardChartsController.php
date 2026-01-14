<?php
namespace Modules\DashboardCharts\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\DashboardCharts\Http\Resources\DashboardOverviewResource;
use Modules\DashboardCharts\Repositories\DashboardRepository;

class DashboardChartsController extends ApiController
{
    private DashboardRepository $repository;

    public function __construct(DashboardRepository $repository)
    {
        $this->repository = $repository;
    }

    public function overview(Request $request)
    {
        try {
            $overviewData = $this->repository->getOverviewData($request);

            return $this->ok(new DashboardOverviewResource($overviewData));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, 500);
        }
    }

}
