<?php
namespace Modules\Language\Database\Seeders;

use Illuminate\Database\Seeder;

class LanguageDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            LanguageSeeder::class,
        ]);
    }
}
