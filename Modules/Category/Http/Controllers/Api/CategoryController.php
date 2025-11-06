<?php

namespace Modules\Category\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Category\DataTables\CategoryApiDataTable;
use Modules\Category\Http\Requests\CategoryGuestRequest;
use Modules\Category\Http\Requests\UpdateCategoryGuestRequest;
use Modules\Category\Http\Resources\CategoryResource;
use Modules\Category\Repositories\CategoryRepository;

class CategoryController extends ApiController
{
    private CategoryRepository $repository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->repository = $categoryRepository;
    }

    /**
     * Display a listing of the resource.
     * @param CategoryApiDataTable $dataTable
     * @return JsonResponse
     */
    public function index(CategoryApiDataTable $dataTable): JsonResponse
    {
        try {
            return $dataTable->ajax();
        } catch (\Exception $e) {
            return $this->fail(__('exceptions.generic'), $e, 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param CategoryGuestRequest $request
     * @return JsonResponse
     */
    public function store(CategoryGuestRequest $request): JsonResponse
    {
        try {
            $category = $this->repository->store($request);

            return $this->ok(new CategoryResource($category), __('category::messages.categories.store', ['name' => $category->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage() ?? __('category::messages.categories.errors.store'), $e, $e->getCode());
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
            $category = $this->repository->showUser($request, $id);

            return $this->ok(new CategoryResource($category));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Update the specified resource in storage.
     * @param UpdateCategoryGuestRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(UpdateCategoryGuestRequest $request, string $id): JsonResponse
    {
        try {
            $category = $this->repository->update($request, $id);

            return $this->ok(new CategoryResource($category), __('category::messages.categories.update', ['name' => $category->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage() ?? __('category::messages.categories.errors.update'), $e, $e->getCode());
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $category = $this->repository->destroy($id, $request);

            return $this->ok(message: __('category::messages.categories.destroy', ['name' => $category->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage() ?? __('category::messages.categories.errors.destroy'), $e, $e->getCode());
        }
    }
}
