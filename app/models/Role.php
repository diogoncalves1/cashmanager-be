<?php

namespace app\Models;

use app\Models\Model;

class Role extends Model
{
    protected $table = "role";

    public function getRoleName($roleId)
    {
        $stmt = $this->conn->query("SELECT name FROM {$this->table} WHERE id = {$roleId}");
        return $stmt->fetchColumn();
    }
}