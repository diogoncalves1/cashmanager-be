<?php

function get_share_request($conn, $user_id)
{
    $query = "SELECT * FROM share_request WHERE user_sent = $user_id";
    $result = $conn->query($query);
    return $result;
}

function delete_share_request($conn, $id)
{
    $query = "DELETE FROM share_request WHERE id = $id";
    $conn->query($query);
}

function get_sent_request($conn, $user_id)
{
    $query = "SELECT * FROM share_request WHERE user_sending = $user_id";
    $result = $conn->query($query);
    return $result;
}
