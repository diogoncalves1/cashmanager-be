<?php

function get_goal_category($conn)
{
    $query = "SELECT * FROM goal_category WHERE 1";
    $result = $conn->query($query);
    return $result;
}