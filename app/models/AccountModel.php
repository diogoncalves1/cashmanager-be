<?php

namespace App\Models;

use PDO;
use app\Models\Model;

class AccountModel extends Model
{
    protected $table = "account";

    // Insert
    public function addAccount(string $nameAccount)
    {
        if ($this->conn->query("INSERT INTO {$this->table} (name) VALUES ('{$nameAccount}')"))
            return $this->conn->lastInsertId();
        return null;
    }

    // Select
    public function getUserAccounts(int $userId)
    {
        $query = "SELECT DISTINCT a.*, au.role_id FROM {$this->table} a 
                    JOIN accounts_user au ON a.id = au.account_id
                    WHERE au.user_id = {$userId}";

        $stmt = $this->conn->query($query);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
    }

    public function getUserAccountsByPermission(int $userId, string $permission)
    {
        $query = "SELECT a.* FROM {$this->table} a
        JOIN accounts_user au ON au.account_id = a.id
        JOIN role_permission rp ON rp.role_id = au.role_id
        JOIN permission p ON rp.permission_id = p.id
        WHERE au.user_id = $userId
        AND p.name = '{$permission}'";

        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAccount(int $accountId)
    {
        $stmt = $this->conn->query("SELECT * FROM {$this->table} WHERE id = $accountId");
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getCashAccount(int $accountId)
    {
        $stmt = $this->conn->query("SELECT cash FROM {$this->table} WHERE id = {$accountId}");
        $cashAccount = $stmt->fetchColumn();

        return $cashAccount ?: null;
    }

    public function getAccountTransactions(int $accountId)
    {
        $stmt = $this->conn->query("SELECT * FROM transactions WHERE account_id = {$accountId} ORDER BY date ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
    }

    public function getNameAccount(int $accountId)
    {
        $stmt = $this->conn->query("SELECT name FROM {$this->table} WHERE id = {$accountId}");
        $accountName = $stmt->fetchColumn();

        return $accountName ?: null;
    }

    // Update
    public function withdrawCashAccount(int $accountId, float $value)
    {
        $stmt = $this->conn->prepare("UPDATE account SET cash = cash - ? WHERE id = ?");
        return $stmt->execute([$value, $accountId]) ?: null;
    }
    public function addCashAccount(int $accountId, float $value)
    {
        $stmt = $this->conn->prepare("UPDATE account SET cash = cash + ? WHERE id = ?");
        return $stmt->execute([$value, $accountId]) ?: null;
    }
    public function updateAccount(int $accountId, string $nameAccount)
    {
        return $this->conn->query("UPDATE {$this->table} SET name = '{$nameAccount}' WHERE id = {$accountId}") ?: null;
    }

    // Delete
    public function deleteAccount($accountId)
    {
        return $this->conn->query("DELETE FROM account WHERE id = {$accountId};") ?: null;
    }
}
