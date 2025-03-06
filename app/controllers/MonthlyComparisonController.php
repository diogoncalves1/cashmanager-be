<?php

namespace app\Controllers;

use app\Controllers\Controller;

class MonthlyComparisonController
{
    public function index()
    {
        return Controller::view("home/monthly-comparison");
    }
}
