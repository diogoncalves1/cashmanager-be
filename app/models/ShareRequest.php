<?php

namespace App\Models;

use app\Models\Model;

class ShareRequest extends Model
{
    protected $table = "share_request";

    public function deleteSharesRequests($type, $objId)
    {
        $stmt = $this->conn->prepare("DELETE FROM share_request WHERE obj_id = ? AND type = ?");
        $stmt->execute([$objId, $type]);
    }

    public function sendRequest(int $type, int $userSent, int $userSending, int $objId, int $roleId)
    {
        $stmt = $this->conn->prepare("INSERT INTO {$this->table}(type, user_sent, user_sending, obj_id, role_id) VALUES (?, ?, ?, ?, ?)");
        $data = [$type, $userSent, $userSending, $objId, $roleId];
        $stmt->execute($data);
    }
}
