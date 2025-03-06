<?php

namespace app\models\Debt;

class Debt
{
    private $conn;

    function __construct($conn)
    {
        $this->conn = $conn;
    }

    function addDebt() {}
    function updateDebt() {}
    function getDebt() {}
}