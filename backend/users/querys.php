<?php

use backend\classes\User;
// GETS
function get_cash($conn, $user_id)
{
    $cash_query = "SELECT cash FROM users WHERE id = $user_id";
    $result_cash = $conn->query($cash_query);
    $row = $result_cash->fetch_assoc();
    $cash = $row['cash'];
    return $cash;
}

function get_shares($conn, $user_id, $type)
{
    switch ($type) {

        case 1: {
                $query = "SELECT DISTINCT a.*, au.user_id
            FROM account a
            JOIN accounts_user au ON a.id = au.account_id
            WHERE au.role_id != 1
            AND EXISTS (
                SELECT 1
                FROM accounts_user au2
                WHERE au2.user_id = $user_id
                AND au2.role_id = 1
                AND au2.account_id = a.id
            )";
                break;
            }

        case 2: {
                $query = "SELECT DISTINCT o.*, ou.user_id
            FROM objective o
            JOIN objective_user ou ON o.id = ou.objective_id
            WHERE ou.role_id != 1
            AND EXISTS (
                SELECT 1
                FROM objective_user ou2
                WHERE ou2.user_id = $user_id
                AND ou2.role_id = 1
                AND ou2.objective_id = o.id
            )";
                break;
            }
        case 3: {
                $query = "SELECT DISTINCT f.*, fu.user_id
            FROM financial_goal f
            JOIN financial_goal_user fu ON f.id = fu.financial_goal_id
            WHERE fu.role_id != 1
            AND EXISTS (
                SELECT 1
                FROM financial_goal_user fu2
                WHERE fu2.user_id = $user_id
                AND fu2.role_id = 1
                AND fu2.financial_goal_id = f.id
            )";
                break;
            }
    }

    $result = $conn->query($query);
    return $result;
}

function is_admin($conn, $id)
{
    $query = "SELECT admin FROM users WHERE id = $id";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row["admin"];
}

function get_user($conn, $id)
{
    $query = "SELECT * FROM users WHERE id = $id";
    $result = $conn->query($query);
    return $result;
}

function get_user_name($conn, $id)
{
    $query = "SELECT name FROM users WHERE id = $id";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row["name"];
}

// UPDATES
function update_cash($conn, $user_id, $cash)
{
    $query = "UPDATE users SET cash = $cash WHERE id = $user_id";
    if ($conn->query($query))
        return 1;
    return 0;
}

function update_settings($conn, $user_id, $name, $email, $password, $coin_id, $light, $notif)
{
    $query = "UPDATE users SET name='$name', email='$email'";
    if ($password != "") {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $query .= ", password = '$hash'";
    }
    $query .= " WHERE id = $user_id";
    $conn->query($query);
    $data = [
        "id" => $user_id,
        "name" => $name,
        "email" => $email,
    ];
    $user = serialize($data);
    setcookie("user", $user, time() + 10 * 365 * 24 * 60 * 60, "/");
    set_coin($conn, $coin_id);
    set_lights($light);
    set_notif($notif);
    return 1;
}

function add_cash($conn, $user_id, $value)
{
    $query = "SELECT cash FROM users WHERE id = $user_id";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $cash = $row['cash'] + $value;
    if (update_cash($conn, $user_id, $cash))
        return 1;
    return 0;
}

function remove_cash($conn, $user_id, $value)
{
    $query = "SELECT cash FROM users WHERE id = $user_id";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $cash = $row['cash'] - $value;
    if (update_cash($conn, $user_id, $cash))
        return 1;
    return 0;
}