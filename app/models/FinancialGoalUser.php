<?php

class FinancialGoalUser
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    function addUserFinancialGoal(int $userId, int $financialGoalId)
    {
        $stmt = $this->conn->prepare("");
    }

    function deleteFinancialGoal(int $financialGoalId)
    {
        $stmt = $this->conn->prepare("DELETE FROM financial_goal_user WHERE financial_goal_id = ?");
        $stmt->execute([$financialGoalId]);
    }
}