<?php

namespace app\Controllers;

use app\Controllers\Controller;
use app\Models\User;
use app\Models\Database;

class AdminController
{
    function index()
    {
        $totalUsers = User::all();
        $numTotalUsers = $totalUsers->rowCount();
        $data = ["numTotalUsers" => $numTotalUsers];
        Controller::view("admin/home", $data);
    }
    public function users()
    {
        $totalUsers = User::all();
        $totalUsers = $totalUsers->fetchAll();
        $data = ["allUsers" => $totalUsers];
        Controller::view("admin/users", $data);
    }
    public function test()
    {
        $query = "SELECT role_id FROM accounts_user WHERE account_id = ? AND user_id = ?";
        $conn = Database::getConn();
        $stmt = $conn->prepare($query);
        $stmt->execute([76, 31]);
        $roleId = $stmt->fetchColumn();
        echo $roleId;
    }
}