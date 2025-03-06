<?php

namespace app\models;

use PDO;
use app\models\Database;
use app\Models\Model;

class Category extends Model
{

    protected $table = "category";

    function getCategory()
    {
        $stmt = $this->conn->prepare("SELECT * FROM category WHERE 1");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategoryName($categoryId)
    {
        $stmt = $this->conn->prepare("SELECT name FROM category WHERE id = {$categoryId}");
        $stmt->execute();
        $category = $stmt->fetch();
        return $category["name"];
    }

    public function getCode($categoryId)
    {
        $stmt = $this->conn->query("SELECT code FROM category WHERE id = {$categoryId}");
        $code = $stmt->fetch();
        return $code["code"];
    }

    public function getCategoryIdByCode(string $categoryName)
    {
        $stmt = $this->conn->query("SELECT id FROM {$this->table} WHERE code = {$categoryName}");
        return $stmt->fetchColumn();
    }

    public function updateCategory($subCategory, $categoryId)
    {
        return $this->conn->query("UPDATE {$this->table} SET `sub-category`={$subCategory} WHERE id = {$categoryId}") ?: 0;
    }
}