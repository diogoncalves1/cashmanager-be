<?php

namespace app\Controllers;

use app\Controllers\Controller;
use app\Models\AuthModel;
use app\Models\Database;

require "../app/core/functions.php";
session_start();

class AuthController
{
    private $authModel;

    public function __construct()
    {
        $this->authModel = new AuthModel();
    }

    function sing_up()
    {
        require_once("../app/core/translate.php");
        if (!isset($_COOKIE['lang'])) {
            $_COOKIE['lang'] = "PT";
            setcookie("lang", "PT", time() + 10 * 365 * 24 * 60 * 60, "/");
        }
        $_SESSION['path'] = $_SERVER['REQUEST_URI'];


        $data = [
            "translate" => $translate
        ];

        Controller::view("auth/sign-up", $data);
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
        require_once("../app/core/translate.php");

        $_SESSION['path'] = $_SERVER['REQUEST_URI'];
        Controller::view("auth/login");
    }
    function test_login($params)
    {
        $email = $params->email;
        $password = $params->password;

        if ($this->authModel->checkEmail($email)) {
            $user = $this->authModel->attempt($email, $password);
            if ($user) {
                $data = [
                    "id" => $user["id"],
                    "name" => $user["name"],
                    "email" => $user["email"],
                    "password" => $user["password"],
                    "cash" => $user["cash"],
                ];
                $data = serialize($data);

                $expiration = time() + 10 * 365 * 24 * 60  * 60;

                setcookie("user", $data, $expiration, "/");
                if (!isset($_COOKIE['lang']))
                    setcookie("lang", "PT", $expiration, "/");
                if (!isset($_COOKIE['mode']))
                    setcookie("mode", "light", $expiration, "/");
                if (!isset($_COOKIE['coin'])) {
                    setcookie("coin_id", "93", $expiration, "/");
                    setcookie("coin_symbol", "€", $expiration, "/");
                }
                if (!isset($_COOKIE['notifications']))
                    setcookie("notifications", 1, $expiration, "/");

                if ($user['admin'])
                    header("location: /CashManager/admin");
                else
                    header("location: /CashManager/home");
            } else {
                header("location: /CashManager/login?e=1");
            }

            $this->authModel->attempt($email, $password);
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
