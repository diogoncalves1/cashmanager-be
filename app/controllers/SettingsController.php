<?php

namespace app\Controllers;

use app\Controllers\Controller;


class SettingsController
{
    function index()
    {
        Controller::view("extras/settings");
    }
    function reminder()
    {
        Controller::view("extras/create_reminder");
    }
    function store_reminder($p)
    {
        require "../backend/querys.php";
        insert_alert_user($conn, $user_id, $p->date, $p->mensage);
        header("location: /CashManager/public/create-reminder");
    }
    function tools()
    {
        Controller::view("extras/tools");
    }
    function update($p)
    {
        session_start();
        require "../backend/querys.php";
        $lightTest = 0;
        $notificationsTest = 0;
        if (isset($_POST['light']))
            $lightTest = 1;
        if (isset($_POST['notifications']))
            $notificationsTest = 1;
        $_SESSION["update"] = update_settings($conn, $user_id, $p->name, $p->email, $p->password, $p->coin, $lightTest, $notificationsTest);
        header("location: /CashManager/settings");
    }
}