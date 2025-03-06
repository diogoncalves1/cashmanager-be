<?php

class GoalCategory
{
    private $conn;
    public function __construct($conn)
    {
        $this->conn = $conn;
    }
}
