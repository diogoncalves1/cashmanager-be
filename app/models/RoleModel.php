<?php

namespace app\Models;

use app\Models\Model;

class RoleModel extends Model
{
    protected $table = "role";

    public function getRoleName($roleId)
    {
        $stmt = $this->conn->query("SELECT name FROM {$this->table} WHERE id = {$roleId}");
        return $stmt->fetchColumn();
    }
    public function getRoleIdByName($roleName)
    {
        $stmt = $this->conn->query("SELECT id FROM {$this->table} WHERE name = '{$roleName}'");
        return $stmt->fetchColumn();
    }
}
