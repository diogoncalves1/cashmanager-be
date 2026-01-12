<?php
namespace Modules\Category\DataTables;

use Modules\Category\Entities\Category;
use Yajra\DataTables\Services\DataTable;

class CategoryApiDataTable extends DataTable
{

    public function dataTable($query)
    {
        $request = request();

        $user = $request->user();

        return datatables()
            ->eloquent($query)
            ->editColumn('name', fn(Category $category) => optional($category->name->{$user->preferences->lang}) ? $category->name->{$user->preferences->lang} : $category->name)
            ->editColumn('type', fn(Category $category) => __('category::attributes.categories.type.' . $category->type))
            ->addColumn('parent', fn(Category $category) => $category->parent)
            ->addColumn('actions', function (Category $category) use ($user) {

                $canEdit    = $category->is_default ? $user->can('authorization', 'editCategoryDefault') : $user->id == $category->id;
                $canDestroy = $category->is_default ? $user->can('authorization', 'destroyCategoryDefault') : $user->id == $category->id;

                return ['edit' => $canEdit, 'destroy' => $canDestroy];
            })
            ->removeColumn('user_id')
            ->removeColumn('parent_id');
    }

    public function query(Category $model)
    {
        $request = request();
        $user    = $request->user();

        $exceptions = ['financialGoalContribution', 'financialGoalWithdrawal'];

        $query = $model->newQuery()
            ->userId($user->id)
            ->orWhere('is_default', 1)
            ->whereNotIn('code', $exceptions);

        if ($request->has('type')) {
            $query->type($request->get('type'));
        }

        return $query;
    }
}
