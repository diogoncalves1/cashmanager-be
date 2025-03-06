<?php

namespace app\Controllers;

use app\Controllers\Controller;
use app\Models\ShareRequest;
use app\Models\User;
use app\Models\Account;
use app\Models\Role;
use PDO;

class ShareController
{
    private $user;

    public function __construct()
    {
        $this->user = new User();
    }

    function index()
    {
        $rolesStmt = Role::all();
        $roles = $rolesStmt->fetchAll(PDO::FETCH_ASSOC);
        $data = ["roles" => $roles];
        Controller::view("extras/share", $data);
    }
    function store($p)
    {
        $shareRequestInstance = new ShareRequest();
        session_start();
        require "../backend/querys.php";
        require "../backend/translate.php";
        $shareRequestInstance->sendRequest($p->type, $p->friend, $this->user->id, $p->obj, $p->role);

        switch ($p->type) {
            case 1: {
                    $t = $words["account"];
                    $accountInstance = new Account();
                    $name = $accountInstance->getNameAccount($p->obj);
                    break;
                }
            case 2: {
                    $t = $words["objective"];
                    $name = get_objective_name_by_id($conn, $p->obj);
                    break;
                }
            case 3: {
                    $t = $words["financial_goal"];
                    $name = get_name_financial_goal($conn, $p->obj);
                    break;
                }
        }
        $_SESSION['alert'] = $words["alert_share_sucess"] . "<br>" . $t . ": " . $name . ".<br>" . $words["user"] . ": " . get_user_name($conn, $p->friend) . ".";

        header("location: /CashManager/share");
    }
    function shares()
    {
        Controller::view("extras/shares");
    }
    function edit()
    {
        Controller::view("extras/edit-share");
    }
    function requests()
    {
        Controller::view("extras/requests");
    }
    function sent_requests()
    {
        Controller::view("extras/sent-requests");
    }
}