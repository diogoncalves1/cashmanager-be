<?php

namespace app\Models;

use app\Models\Model;
use PDO;

class Coin extends Model
{
    protected $table = "coin";

    public function getCoin(int $coinId)
    {
        $stmt = $this->conn->query("SELECT * FROM {$this->table} WHERE id = {$coinId}");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
