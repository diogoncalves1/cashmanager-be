<?php
function insert_alert_user($conn, $user_id, $date, $msg)
{
    $query = "INSERT INTO alert_user(date, user_id, mensage) VALUES('$date', $user_id, '$msg')";
    $conn->query($query);
}

function get_alert_user($conn, $user_id)
{
    $date = new DateTime("now");
    $date = $date->format("Y-m-d");
    $query = "SELECT * FROM alert_user WHERE user_id = $user_id AND date<='$date'";
    $result = $conn->query($query);
    return $result;
}

function delete_alert_user($id)
{
    include("config.php");
    $query = "DELETE FROM alert_user WHERE id = $id";
    $conn->query($query);
}
