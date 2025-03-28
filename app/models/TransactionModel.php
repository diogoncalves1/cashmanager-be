<?php

namespace App\Models;

use app\Models\UserModel;
use app\Models\Alert;
use app\Models\Model;
use PDO;

class TransactionModel extends Model
{
    protected $table = "transactions";

    public function deleteAccountTransactions(int $accountId)
    {
        $stmt = $this->conn->prepare("DELETE FROM transactions WHERE account_id = ?");
        $stmt->execute([$accountId]);
    }

    public function getProof(int $transactionId) {}

    public function getUserTransactions(int $userId)
    {
        $query = "SELECT t.*, au.role_id, a.name AS account,
      ROUND(
        SUM(
            CASE 
                WHEN au.role_id IN (
                    SELECT role_id FROM role_permission rp
                    JOIN permission p ON p.id = rp.permission_id
                    WHERE p.name = 'edit_user_cash'
                ) 
                THEN 
                    CASE 
                        WHEN t.type = 'revenue' THEN t.value 
                        WHEN t.type = 'expense' THEN -t.value 
                        ELSE 0 
                    END
                ELSE 0 
            END
        ) OVER (PARTITION BY t.user_id ORDER BY t.date ASC, t.id ASC), 2
    ) AS total_value
        FROM {$this->table} t
        LEFT JOIN account a ON a.id = t.account_id
        LEFT JOIN accounts_user au ON au.account_id = a.id
        WHERE t.user_id = {$userId} 
        ORDER BY t.date DESC, t.id DESC";

        $stmt = $this->conn->query($query);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getTotalUserRevenue(int $userId)
    {
        $stmt = $this->conn->query("SELECT SUM(value) FROM {$this->table} WHERE user_id = {$userId} AND type = 'revenue'");
        return $stmt->fetchColumn();
    }
    public function getTotalUserExpense(int $userId)
    {
        $stmt = $this->conn->query("SELECT SUM(value) FROM {$this->table} WHERE user_id = {$userId} AND type = 'expense'");
        return $stmt->fetchColumn();
    }
    public function getUserVisibleTransactions(int $userId)
    {
        $query = "SELECT DISTINCT t.*, au.role_id FROM {$this->table} t
                        JOIN accounts_user au ON t.account_id = au.account_id
                        JOIN role r ON au.role_id = r.id
                        JOIN role_permission rp ON r.id = rp.role_id
                        JOIN permission p ON rp.permission_id = p.id
                        WHERE au.user_id = ?
                        AND p.name = ?
                        ORDER BY date ASC, type DESC, id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId, "view_transactions"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
    }

    public function getUserTransactionsWithLimit(int $userId, int $limit, int $offset)
    {

        $query = "SELECT t.*, a.name AS account,
         ROUND(
        SUM(
            CASE 
                WHEN au.role_id IN (
                    SELECT role_id FROM role_permission rp
                    JOIN permission p ON p.id = rp.permission_id
                    WHERE p.name = 'edit_user_cash'
                ) 
                THEN 
                    CASE 
                        WHEN t.type = 'revenue' THEN t.value 
                        WHEN t.type = 'expense' THEN -t.value 
                        ELSE 0 
                    END
                ELSE 0 
            END
        ) OVER (PARTITION BY t.user_id ORDER BY t.date ASC, t.id ASC), 2
    ) AS total_value
        FROM {$this->table} t
        LEFT JOIN account a ON a.id = t.account_id
        JOIN accounts_user au ON au.user_id = t.user_id
        WHERE t.user_id = {$userId} 
        ORDER BY t.date DESC, t.type DESC, t.id DESC
        LIMIT {$limit}  OFFSET {$offset};";


        $stmt = $this->conn->query($query);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addTransaction($proof, int $userId, int $categoryId, string $type, float $value, int $accountId, string $date, string $description, string $to)
    {
        $imageName = "";
        if ($proof != "") {
            if ($proof != 0 && $proof['size'] > 0) {
                $extension = pathinfo($proof['name'], PATHINFO_EXTENSION);
                $imageName = rand(0, 10) . "o" . rand(0, 10) . "O" . rand(0, 10) . "p" . rand(0, 10) . "s" . rand(0, 10) . "P" . rand(0, 10) . "q" . rand(0, 10) . "Q" . rand(0, 10) . "r" . rand(0, 10) . "R" . rand(0, 10) . "s" . rand(0, 10) . "s" . rand(0, 10) . "S" . rand(0, 10) . "t" . rand(0, 10) . "T" . rand(0, 10) . "u" . rand(0, 10) . "U" . rand(0, 10) . "v" . rand(0, 10) . "V" . rand(0, 10) . "w" . rand(0, 10) . "W" . rand(0, 10) . "x" . rand(0, 10) . "X" . rand(0, 10) . "y" . rand(0, 10) . "Y" . rand(0, 10) . "z" . rand(0, 10) . "Z" . "." . $extension;
                $proof['name'] = $imageName;
                move_uploaded_file($proof['tmp_name'], "/CashManager/assets/images/proofs/" . $imageName);
            }
        }

        $stmt = $this->conn->prepare("INSERT INTO transactions(user_id, cat_id, type, value, account_id,date, description, to_p, proof) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $categoryId, $type, $value, $accountId, $date, $description, $to, $imageName]);


        if ($type == "expense") {
            $alertModel = new Alert();
            $alertModel->checkAlert($userId, $categoryId, $value, $date, 0);
            $value -= $value * 2;
        }

        $query = "  SELECT DISTINCT u.id
                    FROM users u
                    JOIN accounts_user au ON u.id = au.user_id
                    JOIN role_permission rp ON au.role_id = rp.role_id
                    JOIN permission p ON rp.permission_id = p.id
                    WHERE p.name = 'edit_user_cash'
                    AND au.account_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$accountId]);
        $result = $stmt->fetchAll();
        foreach ($result as $row) {
            $userModel = new UserModel();
            $userModel->addCash($row["id"], $value);
        }
    }

    public function getTransaction(int $transactionId)
    {
        $stmt = $this->conn->query("SELECT * FROM {$this->table} WHERE id = {$transactionId}");
        return $stmt->fetch();
    }

    public function getUserEditCashTransactionsByDate(int $userId, string $date)
    {
        $query = "SELECT DISTINCT t.* 
        FROM {$this->table} t
        JOIN accounts_user au ON au.user_id = {$userId}
        JOIN role_permission rp ON au.role_id = rp.role_id
        JOIN permission p ON rp.permission_id = p.id
        WHERE p.name = 'edit_user_cash'
        AND t.user_id = au.user_id
        AND t.date = '$date'";

        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getTotalUserRevenueByData(int $userId, string $date)
    {
        $query = "SELECT SUM(value) FROM {$this->table} WHERE user_id = {$userId} AND date >= '{$date}' AND type = 'revenue'";
        $stmt = $this->conn->query($query);
        return $stmt->fetchColumn();
    }
    public function updateTransaction(int $transactionId, float $value, string $date, string $description, string $to, int $categoryId, $proof = 0)
    {
        $imageName = "";

        if ($proof != 0 && $proof['size'] > 0) {
            $extension = pathinfo($proof['name'], PATHINFO_EXTENSION);
            $imageName = rand(0, 10) . "o" . rand(0, 10) . "O" . rand(0, 10) . "p" . rand(0, 10) . "s" . rand(0, 10) . "P" . rand(0, 10) . "q" . rand(0, 10) . "Q" . rand(0, 10) . "r" . rand(0, 10) . "R" . rand(0, 10) . "s" . rand(0, 10) . "s" . rand(0, 10) . "S" . rand(0, 10) . "t" . rand(0, 10) . "T" . rand(0, 10) . "u" . rand(0, 10) . "U" . rand(0, 10) . "v" . rand(0, 10) . "V" . rand(0, 10) . "w" . rand(0, 10) . "W" . rand(0, 10) . "x" . rand(0, 10) . "X" . rand(0, 10) . "y" . rand(0, 10) . "Y" . rand(0, 10) . "z" . rand(0, 10) . "Z" . "." . $extension;
            $proof['name'] = $imageName;
            move_uploaded_file($proof['tmp_name'], "../assets/images/proofs/" . $imageName);
        }

        $update = "UPDATE {$this->table} SET value=?, date = ?, description=?, to_p=?, cat_id = ? ";

        if ($imageName != "")
            $update .= ", proof='$imageName'";

        $update .= " WHERE id = ?";
        $stmt = $this->conn->prepare($update);
        $data = [$value, $date, $description, $to, $categoryId, $transactionId];

        return $stmt->execute($data);
    }

    public function deleteTransaction(int $transactionId)
    {
        return $this->conn->query("DELETE FROM {$this->table} WHERE id = {$transactionId}");
    }

    public function deleteProofExpense() {}

    public function countTransactionsByUser(int $userId)
    {
        $stmt = $this->conn->query("SELECT COUNT(*) FROM {$this->table} WHERE user_id = {$userId}");
        return $stmt->fetchColumn();
    }
}
