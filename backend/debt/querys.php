<?php

function get_debt($conn, $user_id, $id = 0)
{
    $query = "SELECT * FROM debt WHERE user_id = $user_id";
    if ($id != 0)
        $query .= " AND id=$id";
    $result = $conn->query($query);
    return $result;
}

function insert_debt($conn, $user_id, $creditor, $total_value, $rate, $installment, $date, $description, $n_inst, $v_inst, $interest)
{
    $query = "INSERT INTO debt 
    (user_id, creditor, total_value, paid";
    if ($rate != null)
        $query .= ",rate";
    $query .= ", date, installment, status, description";

    if ($interest != null)
        $query .= ", number_inst, value_inst, interest";
    $query .=    ")";
    $query .= "VALUES ($user_id, '$creditor', $total_value, 0";
    if ($rate != null)
        $query .= ",$rate";
    $query .= ",'$date', $installment, 1, '$description'";
    if ($interest != null)
        $query .= ", $n_inst, $v_inst, '$interest'";
    $query .= ")";
    echo $query;
    $conn->query($query);
}

function delete_debt($conn, $id)
{
    $query = "DELETE FROM debt WHERE id = $id";
    $conn->query($query);
    echo "1";
}
function update_debt($conn, $id, $creditor, $total_value, $rate, $installment, $date, $description, $n_inst, $v_inst, $interest)
{

    $query = "UPDATE debt 
    SET creditor='$creditor', total_value=$total_value,";
    if ($rate != null)
        $query .= "rate=$rate,";
    else
        $query .= "rate=NULL, ";
    $query .= "date='$date', 
    description='$description',";
    if ($installment)
        $query .= "installment=$installment,number_inst=$n_inst, value_inst=$v_inst,";
    else
        $query .= "installment=NULL,number_inst=NULL, value_inst=NULL,";
    if ($interest != null)
        $query .= "interest=$interest";
    else
        $query .= "interest=NULL ";
    $query .= "WHERE id=$id";
    echo $query;
    $conn->query($query);
}
