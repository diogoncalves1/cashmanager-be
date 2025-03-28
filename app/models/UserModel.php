<?php

namespace App\Models;

use app\Models\Model;
use PDO;

class UserModel extends Model
{
    protected $table = "users";

    function __construct()
    {
        parent::__construct();
    }

    public function getUserCash(int $userId)
    {
        $stmt = $this->conn->query("SELECT cash FROM users WHERE id = {$userId}");
        return $stmt->fetchColumn();
    }

    public function getUsersCashByAccountPermission(int $accountId, string $permission)
    {
        $query = "SELECT u.cash, u.id FROM users u
        JOIN accounts_user au ON u.id = au.user_id
        JOIN role_permission rp ON au.role_id = rp.role_id
        JOIN permission p ON rp.permission_id = p.id
        WHERE au.account_id = ? AND p.name = ? ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$accountId, $permission]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
    }

    public function updateCash($userId, $cash)
    {
        $stmt = $this->conn->prepare("UPDATE users SET cash = ? WHERE id = ?");
        $stmt->execute([$cash, $userId]);
    }

    public function addCash($userId, $value)
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET cash = cash + ? WHERE id = ?");
        $stmt->execute([$value, $userId]);
    }

    public function withdrawUserCash(int $userId, float $value)
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET cash = cash - ? WHERE id = ?");
        $stmt->execute([$value, $userId]);
    }
}
