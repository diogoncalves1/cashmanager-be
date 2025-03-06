<?php

function get_status_goal($conn)
{
    $query = "SELECT * FROM status_goal WHERE 1";
    $result = $conn->query($query);
    return $result;
}