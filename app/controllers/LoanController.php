<?php

namespace app\Controllers;

use app\Controllers\Controller;

class LoanController
{
    function index()
    {
        Controller::view("loans/manage-loans");
    }
    function add()
    {
        Controller::view("loans/add-loan");
    }
}
