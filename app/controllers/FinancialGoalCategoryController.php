<?php

namespace app\Controllers;

use app\Controllers\Controller;
use app\Models\FinancialGoalCategory;

class FinancialGoalCategoryController
{


    public function index()
    {
        $financialGoalCategorys = FinancialGoalCategory::all();
        $data = ["categories" => $financialGoalCategorys];
        Controller::view("admin/financial-goal-category/manage", $data);
    }
}