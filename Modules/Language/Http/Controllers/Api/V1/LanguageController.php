<?php
namespace Modules\Language\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Log;
use Modules\Language\Http\Resources\LanguageCollection;
use Modules\Language\Repositories\LanguageRepository;

class LanguageController extends ApiController
{
    protected $repository;

    public function __construct(LanguageRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @throws AuthenticationException
     */
    public function index()
    {
        try {
            $langs = $this->repository->all();

            return $this->ok(new LanguageCollection($langs));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail('Error try catch languages', $e, $e->getCode());
        }
    }
}
