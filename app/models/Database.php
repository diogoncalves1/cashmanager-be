<?php

namespace App\Models;

use PDO;
use PDOException;

class Database
{
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "cashmanager_db";
    public $conn;

    function __construct()
    {
        $this->conn = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->username, $this->password);
    }

    public static function getConn()
    {
        $config = require "../config.php";
        $host = $config["host"];
        $dbname = $config["dbname"];
        $username = $config["username"];
        $password = $config["password"];
        try {
            // Corrected the DSN string (use "mysql" instead of "msqli")
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable exceptions for errors
            return $pdo;
        } catch (PDOException $e) {
            // Handle connection error
            echo "Connection failed: " . $e->getMessage();
            return null;
        }
        $pdo = new PDO("msqli:host=$host;dbname=$dbname", $username, $password);
        print_r($config);
        return $pdo;
    }
}
