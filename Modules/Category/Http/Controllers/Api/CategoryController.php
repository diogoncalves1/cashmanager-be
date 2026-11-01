<?php

namespace Modules\Category\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Category\Entities\Category;
use Modules\Category\Http\Requests\CategoryGuestRequest;
use Modules\Category\Http\Requests\UpdateCategoryGuestRequest;
use Modules\Category\Http\Resources\CategoryResource;
use Modules\Category\Services\PersonalCategoryService;

class CategoryController extends ApiController
{
    public function __construct(private PersonalCategoryService $categories) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate(['type' => 'nullable|in:revenue,expense', 'includeArchived' => 'nullable|boolean']);
        $user = $request->user();
        $query = Category::query()->visibleTo($user->id)
            ->where(fn ($query) => $query->whereNull('code')->orWhereNotIn('code', Category::INTERNAL_CODES))
            ->when(! $request->boolean('includeArchived'), fn ($query) => $query->whereNull('archived_at'))
            ->when($request->filled('type'), fn ($query) => $query->type($request->input('type')))
            ->withExists(['transactions', 'recurrings'])->orderByDesc('is_default')->orderBy('id');
        $count = Category::userId($user->id)->default(false)->count();

        return response()->json([
            'success' => true,
            'data' => CategoryResource::collection($query->get()),
            'meta' => ['personalCount' => $count, 'limit' => Category::PERSONAL_LIMIT, 'remaining' => max(0, Category::PERSONAL_LIMIT - $count)],
        ]);
    }

    public function store(CategoryGuestRequest $request): JsonResponse
    {
        $category = $this->categories->store($request->user(), $request->safe()->only(['name', 'type', 'icon', 'color']));

        return $this->ok(new CategoryResource($category->loadExists(['transactions', 'recurrings'])), __('category::messages.categories.store', ['name' => $category->localizedName()]), 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $category = Category::visibleTo($request->user()->id)->withExists(['transactions', 'recurrings'])->findOrFail($id);

        return $this->ok(new CategoryResource($category));
    }

    public function update(UpdateCategoryGuestRequest $request, string $id): JsonResponse
    {
        $category = $this->categories->update($request->user(), $id, $request->safe()->only(['name', 'icon', 'color']));

        return $this->ok(new CategoryResource($category->loadExists(['transactions', 'recurrings'])), __('category::messages.categories.update', ['name' => $category->localizedName()]));
    }

    public function archive(Request $request, string $id): JsonResponse
    {
        $input = $request->validate(['is_archived' => 'required|boolean']);
        $category = $this->categories->archive($request->user(), $id, (bool) $input['is_archived']);

        return $this->ok(new CategoryResource($category->loadExists(['transactions', 'recurrings'])), __('category::messages.categories.'.($category->archived_at ? 'archive' : 'restore')));
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->categories->destroy($request->user(), $id);

        return $this->ok(message: __('category::messages.categories.deleted'));
    }
}
