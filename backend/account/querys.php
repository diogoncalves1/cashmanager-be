<?php

function get_name_account($conn, $account_id)
{
    $query = "SELECT name FROM account WHERE id = $account_id";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row['name'];
}
