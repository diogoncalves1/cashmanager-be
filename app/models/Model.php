<?php

namespace app\Models;

use app\Models\Database;

abstract class Model
{
    protected $conn;

    public function __construct()
    {

        $this->conn = Database::getConn();
    }

    public static function createInstance()
    {
        $instance = new static();
        return $instance;
    }

    public static function all()
    {
        $instance = self::createInstance();
        $stmt = $instance->conn->prepare("SELECT * FROM {$instance->table} WHERE 1");
        $stmt->execute();
        return $stmt;
    }


    public static function add(array $conditions, array $values)
    {
        $instance = self::createInstance();
        $query = "INSERT INTO {$instance->table} (";
        $numValues = count($values);
        for ($i = 0; $i < $numValues; $i++) {
            if ($i > 0)
                $query .= ", ";
            $query .= "$conditions[$i]";
        }
        $query .= ") VALUES (";
        for ($i = 0; $i < $numValues; $i++) {
            if ($i > 0)
                $query .= ", ";
            $query .= " ? ";
        }
        $query .= ")";
        echo $query;
        print_r($values);
        print_r($instance);
        $stmt = $instance->conn->prepare($query);
        $stmt->execute($values);
        return $instance->conn->lastInsertId();
    }

    public static function delete($id)
    {
        $instance = self::createInstance();
        $stmt = $instance->conn->prepare("DELETE FROM {$instance->table} WHERE id = {$id}");
        if ($stmt->execute())
            return 1;
        return 0;
    }
}