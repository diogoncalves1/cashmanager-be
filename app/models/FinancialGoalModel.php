<?php
// Feito
namespace app\Models;

use app\Models\Model;
use PDO;

class FinancialGoalModel extends Model
{
    protected $table = "financial_goal";

    // Select
    public function getUserFinancialGoals(int $userId)
    {
        $query = "SELECT fg.*, fgu.role_id
        FROM {$this->table} fg 
        JOIN financial_goal_user fgu ON fgu.financial_goal_id = fg.id
        WHERE fgu.user_id = {$userId}";

        $stmt = $this->conn->query($query);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getFinancialGoalByUserPermission(int $financialGoalId, int $userId, string $permission)
    {
        $data = [
            $permission,
            $userId,
            $financialGoalId
        ];

        $query = "SELECT fg.* 
        FROM {$this->table} fg 
        JOIN financial_goal_user fgu ON fgu.financial_goal_id = fg.id
        JOIN role_permission rp ON rp.role_id = fgu.role_id
        JOIN permission p ON p.id = rp.permission_id
        WHERE p.name = ?
        AND fgu.user_id = ?
        AND fg.id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($data);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // Insert
    public function addFinancialGoal(float $financialValue, string $financialGoalName, int $category, string $startDate, string $finalDate, int $status, int $priority)
    {
        $data = [
            $financialValue,
            $financialGoalName,
            $category,
            $startDate,
            $finalDate,
            $status,
            $priority
        ];

        $query = "INSERT INTO {$this->table} (value, name, cat_id, start_date, final_date, status_id, priority_id) VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($data);

        return $this->conn->lastInsertId();
    }

    // Update
    public function updateFinancialGoal(int $financialGoalId, float $financialValue, string $financialGoalName, int $category, string $startDate, string $finalDate, int $status, int $priority)
    {
        $data = [
            $financialValue,
            $financialGoalName,
            $category,
            $startDate,
            $finalDate,
            $status,
            $priority,
            $financialGoalId
        ];

        $query = "UPDATE {$this->table} SET value=?, name=?, cat_id=?, start_date=?, final_date=?, status_id=?, priority_id=? WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        return $stmt->execute($data);
    }

    // Delete
    public function deleteFinancialGoal(int $financialGoalId)
    {
        return $this->conn->query("DELETE FROM {$this->table} WHERE id = {$financialGoalId}");
    }
}