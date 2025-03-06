<?php
// GET
function get_objective($conn, $user_id, $objetive_id, $share = 0)
{
    $query = "SELECT * FROM objective WHERE id=$objetive_id AND user_id=$user_id";
    if ($share != 0)
        $query .= " OR id IN (SELECT obj_id FROM shares WHERE user_shared = $user_id AND type = 2)";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        return $result;
    }
}

function get_objectives($conn, $user_id, $view_objective = 0, $edit_objective = 0, $invest = 0, $claimed = -1)
{
    $query = "SELECT DISTINCT o.*, ou.role_id
FROM objective o
JOIN objective_user ou ON o.id = ou.objective_id
JOIN role r ON ou.role_id = r.id
JOIN role_permission rp ON r.id = rp.role_id
JOIN permission p ON rp.permission_id = p.id
WHERE ou.user_id = $user_id";
    if ($view_objective != 0)
        $query .= " AND p.name = 'view_objective' ";
    if ($claimed != -1)
        $query .= " AND o.claimed = $claimed ";
    if ($edit_objective != 0)
        $query .= " AND p.name = 'edit_objective' ";
    if ($invest != 0)
        $query .= " AND p.name = 'invest_objective' ";
    $result = $conn->query($query);
    return $result;
}

function get_all_goals($conn, $user_id, $share = 0)
{
    $query = "SELECT * FROM objective WHERE user_id = $user_id";
    if ($share != 0)
        $query .= " OR id IN (SELECT obj_id FROM shares WHERE user_shared = $user_id AND type = 2)";
    $result = $conn->query($query);
    return $result;
}

function get_objective_name_by_id($conn, $objective_id)
{
    $query = "SELECT name FROM objective WHERE id = $objective_id";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row['name'];
}

function get_now_objective($conn, $objective_id)
{
    $query = "SELECT now FROM objective WHERE id = $objective_id";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row['now'];
}

function get_meta_objective($conn, $id)
{
    $query = "SELECT meta FROM objective WHERE id = $id";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row['meta'];
}

// UPDATE
function update_objective($conn, $objetive_id, $name = 0, $meta = 0, $now = -1, $shared = -1)
{
    $query = "UPDATE objective SET ";
    if ($name !== 0)
        $query .= "name='$name'";
    if ($meta != 0) {
        if ($now == -1)
            $nowObjective = get_now_objective($conn, $objetive_id);
        else
            $nowObjective = $now;
        if ($name != 0)
            $query .= ", ";
        if ($nowObjective == $meta)
            $query .= "claimed = 1";
        else
            $query .= "claimed = 0";
        if ($name != 0)
            $query .= ", ";
        $query .= "meta=$meta ";
    }
    if ($now != -1) {
        if ($meta == -1) {
            $metaObjective = get_meta_objective($conn, $objetive_id);
            if ($name != 0)
                $query .= ", ";
            if ($now == $metaObjective)
                $query .= "claimed = 1";
            else
                $query .= "claimed = 0";
        }
        if ($name != 0 || $meta != 0)
            $query .= ", ";
        $query .= " now=$now";
    }
    if ($shared != -1) {
        if ($name != 0 || $meta != 0 || $now != -1)
            $query .= ", ";
        $query .= " shared=$shared";
    }

    $query .= " WHERE id = $objetive_id";
    echo $query;
    $conn->query($query);
}

// INSERT
function create_new_objective($conn, $user_id, $objectiveName, $objectiveValue)
{
    $query = "INSERT INTO objective(user_id, meta, name) VALUES($user_id, $objectiveValue, '$objectiveName')";
    $conn->query($query);
}

// DELETE
function delete_objetive($id)
{
    include("config.php");

    $query = "SELECT * FROM objective WHERE user_id = $user_id AND id = $id";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $query = "DELETE FROM objective WHERE id = $id";
        if ($conn->query($query))
            echo "1";
        else
            echo "0";
    } else echo "0";
}

// EXTRAS
function reedem_objective($conn, $objective_id)
{
    $query = "UPDATE objective SET claimed=1 WHERE id = $objective_id";
    $conn->query($query);
}