<?php

namespace Modules\Category\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Modules\Category\Repositories\CategoryRepository;
use Modules\Category\DataTables\CategoryDataTable;
use Modules\Category\Http\Requests\CategoryRequest;

class CategoryController extends ApiController
{
    private CategoryRepository $repository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->repository = $categoryRepository;
    }

    /**
     * Display a listing of the resource.
     * @param CategoryDataTable $dataTable
     */
    public function index(CategoryDataTable $dataTable)
    {
        $this->allowedAction('viewCategoryDefault');

        return $dataTable->render('category::admin.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     * @throws AuthorizationException
     */
    public function create(): Renderable
    {
        $this->allowedAction('createCategoryDefault');

        $categories = $this->repository->allAdmin();
        $languages = config('languages');

        return view('category::admin.create', compact('categories', 'languages'));
    }

    /**
     * Store a newly created resource in storage.
     * @param CategoryRequest $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(CategoryRequest $request): RedirectResponse
    {
        $this->allowedAction('createCategoryDefault');

        $category = $this->repository->store($request);

        Session::flash('success', __('category::messages.categories.store', ['name' => $category->name->{app()->getLocale()}]));

        return redirect()->route('admin.categories.index');
    }

    /**
     * Show the form for editing the specified resource.
     * @param string $id
     * @return Renderable
     * @throws AuthorizationException
     */
    public function edit(string $id): Renderable
    {
        $this->allowedAction('editCategoryDefault');

        $category = $this->repository->show($id);
        $categories = $this->repository->allAdmin();
        $languages = config('languages');

        return view('category::admin.create', compact('category', 'categories', 'languages'));
    }

    /**
     * Update the specified resource in storage.
     * @param CategoryRequest $request
     * @param string $id
     * @return RedirectResponse
     */
    public function update(CategoryRequest $request, string $id): RedirectResponse
    {
        $this->allowedAction('editCategoryDefault');

        $category = $this->repository->update($request, $id);

        Session::flash('success', __('category::messages.categories.update', ['name' => $category->name->{app()->getLocale()}]));

        return redirect()->route('admin.categories.index');
    }

    /**
     * Remove the specified resource from storage.
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->allowedAction('destroyCategoryDefault');

            $category = $this->repository->destroy($id);

            return $this->ok(message: __('category::messages.categories.destroy', ['name' => $category->name->{app()->getLocale()}]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }
}
