<?php

namespace app\Controllers;

use app\Controllers\Controller;

class HomeController
{
    public function index()
    {
        return Controller::view("home/home");
    }
}
