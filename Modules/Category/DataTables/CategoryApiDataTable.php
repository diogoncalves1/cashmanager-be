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
            ->editColumn('type', fn(Category $category) => __('category::attributes.categories.type.' . $category->type))
            ->addColumn('parent', fn(Category $category) => $category->parent)
            ->addColumn('actions', function (Category $category) use ($user) {

                $canEdit = $category->default ? $user->can('authorization', 'editCategoryDefault') : $user->id == $category->id;
                $canDestroy = $category->default ? $user->can('authorization', 'destroyCategoryDefault') : $user->id == $category->id;

                return ['edit' => $canEdit, 'destroy' => $canDestroy];
            })
            ->removeColumn('user_id')
            ->removeColumn('parent_id');
    }

    public function query(Category $model)
    {
        $request = request();

        $user = $request->user();

        return $model->newQuery()
            ->userId($user->id)
            ->orWhere('default', 1);
    }
}
