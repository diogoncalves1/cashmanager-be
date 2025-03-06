<?php

namespace App\Models;

use app\Models\Model;
use app\Models\Category;
use PDO;

class Alert extends Model
{
    protected $table = "alert";

    public function getUserAlerts($userId, $date)
    {
        $query = "SELECT * FROM {$this->table} WHERE user_id = ? AND date >= ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId, $date]);
        return $stmt->fetchAll();
    }

    public function getAlert(int $alertId = 0, int $userId, int $categoryId = 0, string $date = "")
    {
        $query = "SELECT * FROM alert WHERE user_id=:user_id ";

        $alertId != 0 ? $params[":id"] = $alertId : null;
        $categoryId != 0 ? $params[":cat_id"] = $categoryId : null;
        $date != 0 ? $params[":date"] = $date : null;

        $conditions = [
            $alertId != 0 ? " AND id = :id " : null,
            $categoryId != 0 ? " AND cat_id = :cat_id " : null,
            $date != 0 ? " AND date >= :date " : null
        ];

        $query .= implode(" ", array_filter($conditions));

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $alert = $stmt->fetch(PDO::FETCH_ASSOC);
        return $alert;
    }

    public function addAlert(int $userId, int $categoryId, int $type, float $diff, string $date)
    {
        $coin = $_COOKIE['coin_symbol'];
        $categoryInstance = new Category();
        $categoryName = $categoryInstance->getCategoryName($categoryId);

        $this->getAlert(0, $userId, $categoryId);

        $alert = $this->getAlert(0, $userId, $categoryId, $date);
        $this->delete($alert["id"]);

        if ($type == 0) {
            $mensage = "Está a " . $diff . $coin . " de chegar ao seu limite da categoria " . $categoryName . "!";
        } else {
            $mensage = "Ultrapassou " . $diff . $coin . " do seu limite da categoria " . $categoryName . "!";
        }

        $conditions = ["user_id", "type", "mensage", "date", "cat_id"];
        $values = [$userId, $type, $mensage, $date, $categoryId];
        $this->add($conditions, $values);
    }

    public function setReadedAlert(int $alertId)
    {
        $this->conn->query("UPDATE alert SET readed = 1 WHERE id = {$alertId}");
    }
    /*
    public function deleteAlert(int $alertId = 0, int $categoryId = 0)
    {
        $date = date("Y-m-01");
        $query = "DELETE FROM alert WHERE date>='$date'";
        if ($alertId != 0)
            $query .= "AND id = {$alertId}";
        if ($categoryId != 0)
            $query .= " AND cat_id = {$categoryId}";
        $this->conn->query($query);
    }
*/
    public function checkAlert($userId, $categoryId, $value, $date, $status) {}
}
