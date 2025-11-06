<?php

namespace Modules\Currency\Database\Seeders;

use Modules\Currency\Entities\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Currency::factory(5)->create();
    }
}
