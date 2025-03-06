<?php

function get_share_type($conn)
{
    $query = "SELECT * FROM share_type WHERE 1";
    $result = $conn->query($query);
    return $result;
}

function get_share_type_name($conn, $id)
{
    $query = "SELECT name FROM share_type WHERE id =$id";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row["name"];
}
