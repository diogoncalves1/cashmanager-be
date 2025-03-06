<?php
function add_schedule_expense($conn, $user_id, $type, $cat_id, $account_id, $value, $date, $description, $to)
{
    echo $type;
    $query = "INSERT INTO scheduled_expenses (user_id, value, date, type, cat_id, account_id, description, to_p) VALUES ($user_id, $value,'$date', $type, $cat_id, $account_id, '$description', '$to')";
    $conn->query($query);
    $id = $conn->insert_id;
    insert_alert_scheduled($conn, $user_id, $date, $id);
}

function get_scheduled_expenses($conn, $user_id, $id = 0, $view = 0, $edit = 0, $edit_user_cash = 0)
{
    $query = "SELECT DISTINCT t.*, au.role_id
FROM scheduled_expenses t
JOIN accounts_user au ON t.account_id = au.account_id
JOIN role r ON au.role_id = r.id
JOIN role_permission rp ON r.id = rp.role_id
JOIN permission p ON rp.permission_id = p.id
WHERE au.user_id = $user_id";
    if ($id != 0)
        $query .= " AND t.id = $id ";
    if ($view != 0)
        $query .= " AND p.name = 'view_transactions' ";
    if ($edit != 0)
        $query .= " AND p.name = 'edit_transactions' ";
    if ($edit_user_cash != 0)
        $query .= " AND p.name = 'edit_user_cash' ";
    $query .= "  ORDER BY date DESC, type ASC, id ASC";

    $result = $conn->query($query);
    return $result;
}


function update_scheduled_expense($conn, $user_id, $expense_id, $value, $date, $description, $to, $cat_id)
{
    $query = "UPDATE scheduled_expenses SET value=$value, date='$date', description='$description', to_p='$to', cat_id='$cat_id' WHERE id = $expense_id AND user_id=$user_id";
    $conn->query($query);
    update_alert_scheduled($conn, $expense_id, $date);
}

function delete_scheduled_expenses($conn, $id)
{
    $query = "DELETE FROM scheduled_expenses WHERE id =$id";
    $conn->query($query);
    delete_alert_scheduled($conn, $id);
}
