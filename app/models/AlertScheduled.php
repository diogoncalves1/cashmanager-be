<?php

namespace app\Models;

use app\Models\Model;

class AlertScheduled extends Model
{
    protected $table = "alert_scheduled";

    public function getUserAlertScheduled($userId)
    {
        $query = "SELECT * FROM alert_scheduled WHERE user_id = $userId";
        if ($_SESSION['page'] != "home")
            $query .= " AND readed = 0";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll();
    }

    public function deleteByScheduledExpenseId(int $ScheduledExpenseId)
    {
        $this->conn->query("DELETE FROM alert_scheduled WHERE scheduled_expenses_id = $ScheduledExpenseId");
    }
}
