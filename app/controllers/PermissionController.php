<?php

namespace app\Controllers;

use app\Controllers\Controller;
use app\Models\Permission;

class PermissionController
{
    public function index()
    {
        $permissions = Permission::all();
        $data = ["permissions" => $permissions];
        Controller::view("admin/permission/manage", $data);
    }
}
