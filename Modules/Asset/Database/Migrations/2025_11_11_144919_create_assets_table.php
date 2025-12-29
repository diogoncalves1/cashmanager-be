<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();

            $table->string('ticker', 191)->unique();
            $table->string('name', 191)->nullable();
            $table->string('country', 191)->nullable();
            $table->string('exchange', 191)->nullable();
            $table->string('sector', 191)->nullable();
            // $table->string('isin', 191)->nullable();
            $table->string('website', 191)->nullable();
            $table->string('logo')->nullable();
            $table->json('price_json')->nullable();
            $table->json('fundamentals_json')->nullable();
            $table->json('dividends_json')->nullable();
            $table->json('dividends_history_json')->nullable();
            $table->tinyInteger('has_dividends')->default(0);
            $table->double('price')->nullable();
            $table->double('price_start_year')->nullable();
            $table->json('price_last_days_history_json')->nullable();
            $table->string('currency', 10)->default('USD');
            $table->json('news_json')->nullable();
            $table->string('type')->nullable();
            // $table->json('info_reit')->nullable();
            $table->timestamp('last_update')->nullable();
            $table->timestamp('last_update_dividends_info')->nullable();
            $table->timestamp('last_update_news')->nullable();
            $table->json('custom_fields')->nullable();

            $table->timestamps();
        });

        $permissions = [
            ['name' => 'Ver Assets', 'code' => 'viewAsset', 'category' => 'Assets'],
            ['name' => 'Adicionar Asset', 'code' => 'createAsset', 'category' => 'Assets'],
            ['name' => 'Editar Asset', 'code' => 'editAsset', 'category' => 'Assets'],
            ['name' => 'Apagar Asset', 'code' => 'destroyAsset', 'category' => 'Assets'],
        ];

        foreach ($permissions as $permission) {
            $id                = DB::table('permissions')->insertGetId($permission);
            $rolePermissions[] = ['permission_id' => $id, 'role_id' => 1];
        }

        DB::table('role_permissions')->insert($rolePermissions);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
