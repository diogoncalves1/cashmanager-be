<?php

namespace App\Models;

use app\Models\Model;

class Objective extends Model
{

    protected $table = "objective";

    public function getObjectivesUser($userId)
    {
        $query = "SELECT DISTINCT o.*, ou.role_id
FROM objective o
JOIN objective_user ou ON o.id = ou.objective_id
JOIN role r ON ou.role_id = r.id
JOIN role_permission rp ON r.id = rp.role_id
JOIN permission p ON rp.permission_id = p.id
WHERE ou.user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getCashInvestedObjective(int $objectiveId)
    {
        $stmt = $this->conn->query("SELECT now FROM {$this->table} WHERE id = {$objectiveId}");
        return $stmt->fetchColumn();
    }

    public function invest(int $objectiveId, float $value)
    {
        //$now = $this->objectiveModel->getCashInvestedObjective($params->objective) + $params->value;
        //update_objective(, $objective_id, 0, 0, $now);
    }

    public function getObjectiveName(int $objectiveId)
    {
        $stmt = $this->conn->query("SELECT name FROM {$this->table} WHERE id = {$objectiveId}");
        return $stmt->fetchColumn();
    }
}
