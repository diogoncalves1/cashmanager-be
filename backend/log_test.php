<?php
session_start();
include_once("config.php");
require_once "classes/User.php";

if (isset($_POST)) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $user = new User($row['id'], $row['name'], $row['email']);
            $user = serialize($user);
            $expiration = time() + 10 * 365 * 24 * 60 * 60;
            setcookie("user", $user, $expiration, "/");
            if (!isset($_COOKIE['lang']))
                setcookie("lang", "PT", $expiration, "/");
            if (!isset($_COOKIE['mode']))
                setcookie("mode", "light", $expiration, "/");
            if (!isset($_COOKIE['coin'])) {
                setcookie("coin_id", "93", $expiration, "/");
                setcookie("coin_symbol", "€", $expiration, "/");
            }
            if (!isset($_COOKIE['light']))
                setcookie("lights", 1, $expiration, "/");
            if (!isset($_COOKIE['notifications']))
                setcookie("notifications", 1, $expiration, "/");
            header("location: ../frontend/index.php");
        } else {
            header("location: ../frontend/login.php?error=1");
        }
    } else {
        header("location: ../frontend/login.php?error=1");
    }
}
