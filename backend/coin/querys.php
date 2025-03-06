<?php

function get_coin($conn)
{
    $query = "SELECT * FROM coin WHERE 1";
    $result = $conn->query($query);
    return $result;
}


?>