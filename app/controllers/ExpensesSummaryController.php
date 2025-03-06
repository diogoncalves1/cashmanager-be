<?php

namespace app\Controllers;

use app\Controllers\Controller;

class ExpensesSummaryController
{
    public function index()
    {
        return Controller::view("home/expenses-summary");
    }
}
