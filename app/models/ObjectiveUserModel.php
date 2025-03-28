<?php
// Feito
namespace app\Models;

use app\Models\Model;

class ObjectiveUserModel extends Model
{
    protected $table = "objective_user";

    // Select
    public function getUserRole(int $objectiveId, int $userId)
    {
        $data = [
            $objectiveId,
            $userId
        ];

        $stmt = $this->conn->prepare("SELECT role_id FROM {$this->table} WHERE objective_id = ? AND user_id = ?");
        $stmt->execute($data);

        return $stmt->fetchColumn();
    }

    // Insert
    public function addObjectiveUser(int $objectiveId, int $userId, int $roleId)
    {
        $data = [
            $objectiveId,
            $userId,
            $roleId
        ];

        $stmt = $this->conn->prepare("INSERT INTO {$this->table} (objective_id, user_id, role_id) VALUES (?, ?, ?)");
        return $stmt->execute($data) ?: null;
    }
}