<?php

function get_priority($conn)
{
    $query = "SELECT * FROM priority WHERE 1";
    $result = $conn->query($query);
    return $result;
}