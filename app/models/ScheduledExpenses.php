<?php

namespace app\Models;

use app\Models\Model;
use PDO;

class ScheduledExpenses extends Model
{
    protected $table = "scheduled_expenses";
    public function getScheduledExpense(int $scheduledExpenseId)
    {
        $stmt = $this->conn->query("SELECT * FROM {$this->table} WHERE id = {$scheduledExpenseId}");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
