<?php
class ObjectiveUser
{
    private $conn;
    public function __construct($conn)
    {
        $this->conn = $conn;
    }
}
