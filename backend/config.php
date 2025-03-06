<?php
$host = "localhost";
$username = "root";
$password = "";
$dbName = "cashmanager_db";

$conn = new mysqli($host, $username, $password, $dbName);

date_default_timezone_set("Europe/Lisbon");
if ($conn->error)
    die("CONECTION ERROR");
if (isset($_COOKIE['lights']))
    $lightTest = $_COOKIE['lights'];
if (isset($_COOKIE['notifications']))
    $notificationsTest = $_COOKIE['notifications'];
if (isset($_COOKIE['user'])) {
    $user = $_COOKIE['user'];
    $user = unserialize($user);
    $coin = $_COOKIE['coin_symbol'];
    $user_id = $user["id"];
    $user_name = $user["name"];
    $user_email = $user["email"];
}

if (!isset($_COOKIE['mode'])) {
    $expiration = time() + 10 * 365 * 24 * 60 * 60;
    setcookie("mode", "light", $expiration, "/");
}
