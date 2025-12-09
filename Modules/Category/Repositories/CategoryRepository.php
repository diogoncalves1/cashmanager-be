<?php
namespace Modules\Category\Repositories;

use App\Http\Controllers\ApiController;
use App\Repositories\RepositoryApiInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Category\Entities\Category;
use Modules\Category\Exceptions\CannotDeleteDefaultCategoryException;
use Modules\Category\Exceptions\CannotDeleteOthersCategoryException;
use Modules\Category\Exceptions\CannotUpdateDefaultCategoryException;
use Modules\Category\Exceptions\CannotUpdateOthersCategoryException;
use Modules\Category\Exceptions\DefaultCategoryNameTranslationRequiredException;
use Modules\Category\Exceptions\UnauthorizedDefaultCategoryException;
use Modules\User\Entities\User;

class CategoryRepository extends ApiController implements RepositoryApiInterface
{
    public function all()
    {
        return Category::all();
    }

    public function allAdmin()
    {
        return Category::default(1)->get();
    }

    public function allUser(User $user)
    {
        $categories = Category::default(1)
            ->orWhere('user_id', $user->id)
            ->get();

        $userLang = $user->preferences->lang;

        foreach ($categories as &$category) {
            $category->name = $category->name->{$userLang} ?? $category->name;
        }

        return $categories;
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {

            $input = $request->only(['type', 'icon', 'color', 'parent_id']);

            $user = Auth::user() ? Auth::user() : $request->user();

            $input["name"] = $request->get('name');

            if (! $request->get('is_default')) {
                $input['user_id'] = $user->id;
            } else {
                if (! $user || ! $user->can('authorization', 'createCategoryDefault')) {
                    throw new UnauthorizedDefaultCategoryException();
                }
                $input['code']       = $request->get('code');
                $input['is_default'] = 1;
            }

            $category = Category::create($input);

            Log::info('Category ' . $category->id . ' successfully created.');
            return $category;
        });
    }

    public function update(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $category = $this->show($id);

            $input = $request->only(['type', 'icon', 'name', 'color', 'parent_id']);
            $user  = Auth::user() !== null ? Auth::user() : $request->user();

            if ($category->is_default) {
                if (! is_array($request->get('name'))) {
                    throw new DefaultCategoryNameTranslationRequiredException();
                }
                if (! $user->can('authorization', 'editCategoryDefault')) {
                    throw new CannotUpdateDefaultCategoryException();
                }
                $input['code'] = $request->get('code');
            }

            if (! $user || ! $category->is_default && $category->user_id != $user->id) {
                throw new CannotUpdateOthersCategoryException();
            }

            $category->update($input);

            return $category;
        });
    }

    public function destroy(Request $request, string $id)
    {
        return DB::transaction(function () use ($id, $request) {
            $category = $this->show($id);

            $user = Auth::user() !== null ? Auth::user() : $request->user();

            if (! $user || ! $category->is_default && $category->user_id != $user->id) {
                throw new CannotDeleteOthersCategoryException();
            }

            if ($category->is_default && ! $user->can('authorization', 'destroyCategoryDefault')) {
                throw new CannotDeleteDefaultCategoryException();
            }

            $category->delete();

            return $category;
        });
    }

    public function show(string $id)
    {
        return Category::find($id);
    }

    public function showUser(Request $request, string $id)
    {
        $user = Auth::user() ? Auth::user() : $request->user();

        $category = $this->show($id);

        if (! $category->is_default && $user->id !== $category->user_id) {
            throw new AuthorizationException('This action is unauthorized');
        }

        return $category;
    }
}
