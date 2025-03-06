<?php

namespace app\Models;

use app\Models\Model;

class Loan extends Model
{
    protected $table = "loan";
    /*
    public function addLoan(int $userId, float $value, string $institution, float $rate, string $term, string $initialDate, int $numberInstallments, string $warranty)
    {
        $data = ["userId" => $userId, "value" => $value];

        $this->conn->prepare("INSERT INTO {$this->table} () VALUES {}");
    }*/
}