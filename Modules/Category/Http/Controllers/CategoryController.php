<?php

namespace Modules\Category\Http\Controllers;

use App\Http\Controllers\AppController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Auth;
use Modules\Category\Repositories\CategoryRepository;

class CategoryController extends AppController
{
    private CategoryRepository $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(): Renderable
    {
        return view('category::frontend.categories.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(): Renderable
    {
        $categories = $this->categoryRepository->allUser(Auth::user());

        return view('category::frontend.categories.form', compact('categories'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param string $id
     * @return Renderable
     */
    public function edit(string $id): Renderable
    {
        $category = $this->categoryRepository->show($id);

        $categories = $this->categoryRepository->allUser(Auth::user());

        return view('category::frontend.categories.form', compact('category', 'categories'));
    }
}
