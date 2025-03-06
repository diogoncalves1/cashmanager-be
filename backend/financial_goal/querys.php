<?php

function insert_financial_goal($conn, $user_id, $value, $name, $cat_id, $start_date, $final_date, $status_id, $priority_id)
{
    $query = "INSERT INTO financial_goal(user_id, value, name, cat_id, start_date, final_date, status_id, priority_id) VALUES($user_id, $value, '$name', $cat_id, '$start_date', '$final_date', $status_id, $priority_id)";
    if ($conn->query($query))
        return 1;
    else
        return 0;
}

function get_financial_goal($conn, $user_id, $id = 0, $view = 0, $edit = 0, $edit_invest = 0, $invest = 0)
{
    $query = "SELECT DISTINCT f.*, fu.role_id
FROM financial_goal f
JOIN financial_goal_user fu ON f.id = fu.financial_goal_id
JOIN role r ON fu.role_id = r.id
JOIN role_permission rp ON r.id = rp.role_id
JOIN permission p ON rp.permission_id = p.id
WHERE fu.user_id = $user_id";
    if ($id != 0)
        $query .= " AND f.id = $id ";
    if ($view != 0)
        $query .= " AND p.name = 'view_financial_goals' ";
    if ($edit_invest != 0)
        $query .= " AND p.name = 'edit_invest' ";
    if ($invest != 0)
        $query .= " AND p.name = 'invest_financial_goal' ";
    if ($edit != 0)
        $query .= " AND p.name = 'edit_financial_goal' ";
    $result = $conn->query($query);
    return $result;
}

function get_name_financial_goal($conn, $id)
{
    $query = "SELECT name FROM financial_goal WHERE id = $id";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row['name'];
}

function delete_financial_goal($id)
{
    include("config.php");

    $query = "DELETE FROM financial_goal WHERE id = $id";
    $conn->query($query);

    $response = "";
    if ($_COOKIE['mode'] == "ligth") {
        $color = "table-primary";
    } else
        $color = "";
    $result_accounts = get_financial_goal($conn, $user_id);
    while ($row = mysqli_fetch_assoc($result_accounts)) {
        $response .= '<tr class="' . $color . ' primary">
            <td>' . $row["name"] . '</td>
            <td>' . $row['value'] . $coin . '</td>
            <td>' . $row['earned_value'] . $coin . '</td>
            <td>' . $row['value'] - $row['earned_value'] . $coin . '</td>
            <td><button style="background: none; border: none;"
                    onclick="goToEdit(' . $row['id'] . ')"><svg class="bi">
                        <use xlink:href="#edit" />
                    </svg></button></td>
            <td class="center"><button data-bs-toggle="modal" data-bs-target="#delete-modal"
                    style="background: none; border: none;"
                    onclick="modal(' . $row['id'] . ')"><svg class="bi">
                        <use xlink:href="#delete" />
                    </svg></button></td>
        </tr>';
    }
    echo $response;
}

function update_financial_goal($conn, $id, $user_id, $value = -1, $name = 0, $initial_date = 0, $final_date = 0, $cat_id = 0, $status_id = 0, $priority_id = 0, $shared = -1)
{
    $query = "UPDATE financial_goal SET ";
    if ($value != -1)
        $query .= "value=$value";
    if ($name != 0) {
        if ($value != -1)
            $query .= ",";
        $query .= " name='$name'";
    }
    if ($initial_date != 0) {
        if ($name != 0 || $value != -1)
            $query .= ",";
        $query .= " start_date='$initial_date'";
    }
    if ($final_date != 0) {
        if ($value != -1 || $name != 0 || $initial_date != 0)
            $query .= ",";
        $query .= " final_date='$final_date'";
    }
    if ($cat_id != 0) {
        if ($value != -1 || $name != 0 || $initial_date != 0 || $final_date != 0)
            $query .= ",";
        $query .= " cat_id=$cat_id";
    }
    if ($status_id != 0) {
        if ($value != -1 || $name != 0 || $initial_date != 0 || $final_date != 0 || $cat_id != 0)
            $query .= ",";
        $query .= " status_id=$status_id";
    }
    if ($priority_id != 0) {
        if ($value != -1 || $name != 0 || $initial_date != 0 || $final_date != 0 || $cat_id != 0 || $status_id != 0)
            $query .= ",";
        $query .= " priority_id=$priority_id";
    }
    if ($shared != -1) {
        if ($value != -1 || $name != 0 || $initial_date != 0 || $final_date != 0 || $cat_id != 0 || $status_id != 0 || $priority_id != 0)
            $query .= ",";
        $query .= " shared = $shared";
    }

    $query .= " WHERE id=$id AND user_id = $user_id";
    echo $query;
    if ($conn->query($query))
        return 1;
    return 0;
}
