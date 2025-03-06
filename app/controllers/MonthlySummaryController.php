<?php

namespace app\Controllers;

use app\Controllers\Controller;

class MonthlySummaryController
{
    public function index()
    {
        return Controller::view("home/monthly-summary");
    }
}