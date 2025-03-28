<?php
// Feito 
namespace app\Models;

use app\Models\Model;

class FinancialGoalUserModel extends Model
{
    protected $table = "financial_goal_user";

    // Select
    public function getUserRole(int $financialGoalId, int $userId)
    {
        $query = "SELECT role_id FROM {$this->table} WHERE financial_goal_id = {$financialGoalId} AND user_id = {$userId}";
        $stmt = $this->conn->query($query);

        return $stmt->fetchColumn();
    }

    // Insert
    function addUserFinancialGoal(int $userId, int $financialGoalId, int $roleId)
    {
        $data = [
            $userId,
            $financialGoalId,
            $roleId
        ];

        $stmt = $this->conn->prepare("INSERT INTO {$this->table} (user_id, financial_goal_id, role_id) VALUES (?, ?, ?)");
        return $stmt->execute($data);
    }
}