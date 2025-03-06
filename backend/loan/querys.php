<?php

function get_loan($conn, $user_id)
{
    $query = "SELECT * FROM loan WHERE user_id =$user_id";
    $result = $conn->query($query);
    return $result;
}
