<?php

function delete_friend($id)
{
    include("config.php");
    $query = "DELETE FROM friend WHERE user_2 = $user_id AND user_1 = $id OR user_1 = $user_id AND user_2 = $id";
    $conn->query($query);
}

function get_friends($conn, $user_id)
{
    $query = "SELECT * FROM friend WHERE status = 1 AND user_1 = $user_id OR user_2 = $user_id ";
    $result = $conn->query($query);
    return $result;
}

function get_friend_request($conn, $user_id, $send = 0, $status = -1)
{
    $query = "SELECT * FROM friend WHERE";
    if ($send == 1)
        $query .= " user_1 = $user_id";
    else
        $query .= " user_2 = $user_id";
    if ($status != -1) {
        $query .= " AND status = $status";
    }
    $result = $conn->query($query);
    return $result;
}

function send_fried_request($id, $remove)
{
    include("config.php");
    if ($remove == 1) {
        $query = "DELETE FROM friend WHERE user_1 = $user_id AND user_2 = $id";
    } else {
        $query = "INSERT INTO friend (status, user_1,user_2) VALUES(0, $user_id, $id)";
    }
    echo "1";
    $conn->query($query);
}
function set_friend_request($id, $accept)
{
    include("config.php");
    if ($accept == 0) {
        $query = "DELETE FROM friend WHERE user_2 = $user_id AND user_1 = $id";
    } else {
        $query = "UPDATE friend SET status=1 WHERE user_2 = $user_id AND user_1 = $id";
    }
    $conn->query($query);
}