<?php

function delete_share_obj($conn, $objective_id, $user_id)
{
    $query = "DELETE FROM objective_user 
    WHERE user_id = $user_id
    AND objective_id = $objective_id";
    echo $query;
    $conn->query($query);
}

function delete_share_f_goal($conn, $f_goal, $user_id)
{
    $query = "DELETE FROM financial_goal_user 
    WHERE user_id = $user_id
    AND financial_goal_id = $f_goal";
    $conn->query($query);
}
