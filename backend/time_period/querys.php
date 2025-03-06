<?php

function get_time_period($conn)
{
    $query = "SELECT * FROM time_period WHERE 1";
    $result = $conn->query($query);
    return $result;
}