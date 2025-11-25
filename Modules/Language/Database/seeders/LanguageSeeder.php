<?php
namespace Modules\Language\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Language\Entities\Language;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Language::factory(2)->create();
    }
}
