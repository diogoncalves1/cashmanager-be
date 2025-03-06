<?php

namespace app\Controllers;

use app\Controllers\Controller;

class FriendController
{
    function index()
    {
        Controller::view("extras/friends");
    }
    function add()
    {
        Controller::view("extras/add-friend");
    }
}
