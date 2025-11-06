<?php

namespace Modules\Currency\Database\Seeders;

use Modules\Currency\Database\seeders\CurrencySeeder;
use Illuminate\Database\Seeder;

class CurrencyDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            CurrencySeeder::class
        ]);
    }
}
