<?php
function get_role($conn)
{
    $query = "SELECT * FROM role WHERE 1";
    $result = $conn->query($query);
    return $result;
}

function get_role_name($conn, $role_id)
{
    $query = "SELECT name FROM role WHERE id=$role_id";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row["name"];
}