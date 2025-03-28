<?php

namespace App\Models;

use app\Models\Model;

class AccountUserModel extends Model
{
    protected $table = "accounts_user";

    // Insert
    public function addAccountUser(int $accountId, int $userId, int $roleId)
    {
        return $this->conn->query("INSERT INTO {$this->table} (account_id, user_id, role_id) VALUES ({$accountId}, {$userId}, {$roleId})") ?: null;
    }

    // Select
    public function getUserRole(int $accountId, int $userId)
    {
        $query = "SELECT role_id FROM accounts_user WHERE account_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$accountId, $userId]);
        $roleId = $stmt->fetchColumn();
        return $roleId;
    }

    // Delete
    public function removeUserAccount(int $accountId, int $userId)
    {
        $query = "DELETE FROM accounts_user WHERE user_id = {$userId} AND account_id = {$accountId}";
        return $this->conn->query($query) ?: null;
    }

    public function removeAllAccount(int $accountId)
    {
        return $stmt = $this->conn->query("DELETE FROM {$this->table} WHERE account_id = {$accountId}") ?: null;
    }
}
