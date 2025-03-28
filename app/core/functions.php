<?php

use app\Models\Database;

function hasPerm($role_id, $permission_name)
{
    $conn = Database::getConn();

    $query = "SELECT *
        FROM permission p
        JOIN role_permission rp
        ON p.id = rp.permission_id
        WHERE rp.role_id = ?
        AND p.name = ?;";
    $stmt = $conn->prepare($query);
    $stmt->execute([$role_id, $permission_name]);
    if ($stmt->rowCount() > 0)
        return 1;
    return 0;
}


function is_admin($id)
{
    $conn = Database::getConn();

    $stmt = $conn->query("SELECT admin FROM users WHERE id = {$id}");
    return $stmt->fetchColumn();
}

function porcent($now, $meta)
{
    $porcent = ($now / $meta) * 100;
    $porcent = number_format($porcent, 2);
    return $porcent;
}
