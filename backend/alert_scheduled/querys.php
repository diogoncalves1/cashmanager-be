<?php
function get_alert_scheduled($conn, $user_id)
{
    $query = "SELECT * FROM alert_scheduled WHERE user_id = $user_id";
    if ($_SESSION['page'] != "home")  // SE A PÁGINA ATUA NÃO FOR O "HOME" RETORNAR APENAS AS "NÃO LIDAS"
        $query .= " AND readed = 0";
    $result = $conn->query($query);
    return $result;
}

function delete_alert_scheduled($conn, $id)
{
    $query = "DELETE FROM alert_scheduled WHERE scheduled_expenses_id = $id";
    $conn->query($query);
}

function update_alert_scheduled($conn, $scheduled_id, $date)
{
    $query = "UPDATE alert_scheduled date = '$date'";
    $conn->query($query);
}

function insert_alert_scheduled($conn, $user_id, $date, $id)
{
    $query = "INSERT INTO alert_scheduled (user_id, date, scheduled_expenses_id) VALUES ($user_id, '$date', $id) ";
    $conn->query($query);
}

function set_readed_alert_scheduled($id)
{
    include("config.php");
    $query = "UPDATE alert_scheduled SET readed=1";
    $conn->query($query);
}
