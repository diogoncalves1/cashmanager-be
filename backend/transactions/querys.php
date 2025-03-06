<?php
// GET
function get_transactions($conn, $user_id, $id = 0, $view_transactions = 0, $edit_transaction = 0, $edit_user_cash = 0, $date = 0, $type = -1, $dateG = 0)
{
    $query = "SELECT DISTINCT t.*, au.role_id
FROM transactions t
JOIN accounts_user au ON t.account_id = au.account_id
JOIN role r ON au.role_id = r.id
JOIN role_permission rp ON r.id = rp.role_id
JOIN permission p ON rp.permission_id = p.id
WHERE au.user_id = $user_id";
    if ($id != 0)
        $query .= " AND t.id = $id ";
    if ($date != 0)
        $query .= " AND t.date = '$date' ";
    if ($dateG != 0)
        $query .= " AND t.date >= '$dateG' ";
    if ($type != -1)
        $query .= " AND t.type = $type ";
    if ($view_transactions != 0)
        $query .= " AND p.name = 'view_transactions' ";
    if ($edit_transaction != 0)
        $query .= " AND p.name = 'edit_transactions' ";
    if ($edit_user_cash != 0)
        $query .= " AND p.name = 'edit_user_cash' ";
    $query .= "  ORDER BY date DESC, type ASC, id ASC";
    $result = $conn->query($query);

    return $result;
}

function delete_photo_expense($id)
{
    include("config.php");
    $query = "SELECT proof FROM transactions WHERE id =$id ";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $proofName = $row["proof"];
    unlink("../assets/images/proofs/" . $proofName);
    $query = "UPDATE transactions SET proof=null";
    $conn->query($query);
}
