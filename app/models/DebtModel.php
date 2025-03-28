<?php

namespace app\Models;

use app\Models\Model;
use PDO;

class DebtModel extends Model
{
    protected $table = "debt";

    public function getUserDebts(int $userId)
    {
        $query = "SELECT d.* 
        FROM {$this->table} d
        JOIN debt_user du ON du.debt_id = d.id
        WHERE du.user_id = $userId";

        $stmt = $this->conn->query($query);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}