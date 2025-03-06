<?php
include("../backend/config.php");

function hasPerm($conn, $role_id, $permission_name)
{
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
