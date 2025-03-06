<?php

namespace app\models\FinancialGoal;

class FinancialGoal
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function addFinancialGoal() {}
    public function updateFinancialGoal() {}
    public function deleteFinancialGoal() {}
    public function getFinancialGoal() {}
}