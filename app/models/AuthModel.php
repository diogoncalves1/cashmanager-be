<?php

namespace app\Models;

use app\Models\Model;
use PDO;

class AuthModel extends Model
{

    public static function checkLogin()
    {
        if (isset($_COOKIE["user"])) {
            return 1;
        } else {
            header("location: ../app/views/auth/login");
        }
    }

    public function checkEmail(string $email)
    {
        $stmt = $this->conn->query("SELECT * FROM users WHERE email = '{$email}'");

        return $stmt->rowCount();
    }

    public function attempt(string $email, string $password)
    {
        $stmt = $this->conn->query("SELECT * FROM users WHERE email = '{$email}'");

        if ($stmt->rowCount()) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $user["password"])) {
                return $user;
            }
        }
        return 0;
    }
}
