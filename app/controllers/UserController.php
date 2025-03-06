<?php

namespace app\Controllers;

use app\Controllers\Controller;
use League\Plates\Template\Name;
use backend\classes\User;

class UserController
{
    function sing_up()
    {
        Controller::view("sign-up");
    }
    function test_sing_up($params)
    {
        require "../backend/querys.php";
        $email = $params->email;
        $password = $params->password;
        $email_test = "SELECT * FROM users WHERE email = '$email'";
        $name = $_POST['name'];

        $result_email_test = $conn->query($email_test);
        if ($result_email_test->num_rows > 0) {
            $error = 1;
            header("location: /CashManager/sign-up?e=1");
        } else {
            $password_encript = password_hash($password, PASSWORD_DEFAULT);
            $insert = "INSERT INTO users(email, password, name) VALUES('$email','$password_encript', '$name')";
            $conn->query($insert);
            $new_user_query = "SELECT id FROM users WHERE email = '$email'";
            $result_new_user = $conn->query($new_user_query);
            while ($row = $result_new_user->fetch_assoc()) {
                $data = [
                    "id" => $row["id"],
                    "name" => $row["name"],
                    "email" => $row["email"],
                    "password" => $row["password"],
                    "cash" => $row["cash"],
                ];
            }
            $data = serialize($data);
            setcookie("user", $data, time() + 10 * 365 * 24 * 60 * 60, "/");
            if (!isset($_COOKIE['lang']))
                setcookie("lang", "PT", $expiration, "/");
            if (!isset($_COOKIE['mode']))
                setcookie("mode", "light", $expiration, "/");
            if (!isset($_COOKIE['notifications']))
                setcookie("notifications", 1, $expiration, "/");
            if (!isset($_COOKIE['coin'])) {
                setcookie("coin_id", "93", $expiration, "/");
                setcookie("coin_symbol", "€", $expiration, "/");
            }
            if (!isset($_COOKIE['light']))
                setcookie("lights", 1, $expiration, "/");
            header("location: /CashManager/home");
        }
    }
    function login()
    {
        Controller::view("login");
    }
    function test_login($params)
    {
        include("../backend/config.php");
        $email = $params->email;
        $password = $params->password;
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($query);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $data = [
                    "id" => $row["id"],
                    "name" => $row["name"],
                    "email" => $row["email"],
                    "password" => $row["password"],
                    "cash" => $row["cash"],
                ];
                $data = serialize($data);
                setcookie("user", $data, time() + 10 * 365 * 24 * 60 * 60, "/");
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
                if ($row['admin'])
                    header("location: /CashManager/admin");
                else
                    header("location: /CashManager/home");
            } else {
                header("location: /CashManager/login?e=1");
            }
        } else {
            header("location: /CashManager/login?e=1");
        }
    }

    function logout()
    {
        setcookie("user", "", time() - 3600, "/");
        header("location: /CashManager/sign-up");
    }
}
