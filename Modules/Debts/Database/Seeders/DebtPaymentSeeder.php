<?php

namespace Modules\Debts\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Debts\Entities\DebtPayment;

class DebtPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DebtPayment::factory(3)->create();
    }
}
