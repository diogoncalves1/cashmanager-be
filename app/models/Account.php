<?php

namespace App\Models;

use PDO;
use app\Models\Model;

class Account extends Model
{
    protected $table = "account";

    public function getUserAccounts(int $userId)
    {
        $query = "SELECT DISTINCT a.*, au.role_id FROM {$this->table} a 
                    JOIN accounts_user au ON a.id = au.account_id
                    WHERE au.user_id = {$userId}";

        $stmt = $this->conn->query($query);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
    }

    public function getAccount(int $accountId, int $userId)
    {
        $query = "SELECT DISTINCT a.*, au.role_id FROM {$this->table} a 
                    JOIN accounts_user au 
                    ON a.id = au.account_id
                    WHERE au.user_id = ? AND a.id = ?;";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId, $accountId]);
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
        $stmt = $this->conn->prepare("UPDATE account SET name = ? WHERE id = ?");
        return $stmt->execute([$nameAccount, $accountId]) ?: null;
    }

    public function deleteAccount($accountId)
    {
        return $this->conn->query("DELETE FROM account WHERE id = {$accountId};") ?: null;
    }
}
