<?php

class Friend
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }
}
