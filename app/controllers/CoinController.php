<?php

namespace app\Controllers;

use app\Controllers\Controller;
use app\Models\Coin;

class CoinController
{
    private $coinInstance;

    function __construct()
    {
        $this->coinInstance = new Coin();
    }

    public function index()
    {
        $coins = Coin::all();
        Controller::view("admin/coin/manage", ["coins" => $coins]);
    }
}
