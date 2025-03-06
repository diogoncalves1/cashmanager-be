<?php

namespace app\Controllers;

use app\Controllers\Controller;

class YearComparisonController
{
    public function index()
    {
        return Controller::view("home/year-comparison");
    }
}
