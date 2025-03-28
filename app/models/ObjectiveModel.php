<?php
// Feito
namespace App\Models;

use app\Models\Model;
use PDO;

class ObjectiveModel extends Model
{
    protected $table = "objective";

    // Select
    public function getUserObjectives(int $userId)
    {
        $query = "SELECT o.*, ou.role_id 
        FROM {$this->table} o
        JOIN objective_user ou ON ou.objective_id = o.id
        WHERE ou.user_id = $userId";

        $stmt = $this->conn->query($query);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getUserObjectivesNotClaimed(int $userId)
    {
        $query = "SELECT o.*, ou.role_id 
        FROM {$this->table} o
        JOIN objective_user ou ON ou.objective_id = o.id
        WHERE ou.user_id = $userId
        AND o.claimed = 0";

        $stmt = $this->conn->query($query);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getUserObjectivesCompleted(int $userId)
    {
        $query = "SELECT o.*, ou.role_id 
        FROM {$this->table} o
        JOIN objective_user ou ON ou.objective_id = o.id
        WHERE ou.user_id = $userId
        AND o.claimed = 1";

        $stmt = $this->conn->query($query);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getObjective(int $objectiveId)
    {
        $stmt = $this->conn->query("SELECT * FROM {$this->table} WHERE id = {$objectiveId}");

        return $stmt->fetch();
    }
    public function getObjectiveByUserPermission(int $objectiveId, int $userId, string $permission)
    {
        $data = [
            $userId,
            $objectiveId,
            $permission
        ];

        $query = "SELECT o.*
        FROM {$this->table} o 
        JOIN objective_user ou ON ou.objective_id = o.id
        JOIN role_permission rp ON rp.role_id = ou.role_id
        JOIN permission p ON p.id = rp.permission_id  
        WHERE ou.user_id = ?
        AND o.id = ?
        AND p.name = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($data);

        return $stmt->fetch() ?: null;
    }
    public function getUserObjectivesByPermissionNotClaimed(int $userId, string $permission)
    {
        $data = [
            $userId,
            $permission
        ];

        $query = "SELECT o.*, ou.role_id
        FROM {$this->table} o
        JOIN objective_user ou ON ou.objective_id = o.id
        JOIN role_permission rp ON rp.role_id = ou.role_id
        JOIN permission p ON p.id = rp.permission_id
        WHERE ou.user_id = ?
        AND p.name = ?
        AND o.claimed = 0";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($data);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getObjectiveAmountIvested(int $objectiveId)
    {
        $stmt = $this->conn->query("SELECT amount_invested FROM {$this->table} WHERE id = {$objectiveId}");

        return $stmt->fetchColumn();
    }

    // Insert
    public function addObjective(string $objectiveName, float $objectiveValue)
    {
        $data = [
            $objectiveName,
            $objectiveValue
        ];

        $stmt = $this->conn->prepare("INSERT INTO {$this->table} (name, objective_value) VALUES (?, ?)");
        $stmt->execute($data);

        return $this->conn->lastInsertId();
    }

    // Update
    public function updateObjective(int $objectiveId, string $objectiveName, float $objectiveValue)
    {
        $data = [
            $objectiveName,
            $objectiveValue,
            $objectiveId
        ];

        $stmt = $this->conn->prepare("UPDATE {$this->table} SET name = ?, objective_value = ? WHERE id = ?");

        return $stmt->execute($data);
    }
    public function investObjective(int $objectiveId, float $valueToInvest)
    {
        $data = [
            $valueToInvest,
            $objectiveId
        ];

        $stmt = $this->conn->prepare("UPDATE {$this->table} SET amount_invested	 = amount_invested	 + ? WHERE id = ?");
        return $stmt->execute($data);
    }
    public function markObjectiveCompleted(int $objectiveId)
    {
        return $this->conn->query("UPDATE {$this->table} SET claimed = 1 WHERE id = {$objectiveId}");
    }
    public function markObjectiveIncompleted(int $objectiveId)
    {
        return $this->conn->query("UPDATE {$this->table} SET claimed = 0 WHERE id = {$objectiveId}");
    }

    // Delete
    public function deleteObjective(int $objectiveId)
    {
        return $this->conn->query("DELETE FROM {$this->table} WHERE id = {$objectiveId}");
    }
}