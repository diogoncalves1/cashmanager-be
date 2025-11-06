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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3);
            $table->string('symbol', 50);
            $table->json('name');
            $table->decimal("rate", 15, 4)->default(0);
            $table->timestamps();
        });

        $permissions = [
            ['name' => 'Ver Moedas', 'code' => 'viewCurrency', 'category' => 'Moedas'],
            ['name' => 'Adicionar Moedas', 'code' => 'createCurrency', 'category' => 'Moedas'],
            ['name' => 'Editar Moedas', 'code' => 'editCurrency', 'category' => 'Moedas'],
            ['name' => 'Apagar Moedas', 'code' => 'destroyCurrency', 'category' => 'Moedas'],
            ['name' => 'Atualizar Taxa das Moedas', 'code' => 'updateRates', 'category' => 'Moedas'],
        ];

        foreach ($permissions as $permission) {
            $id = DB::table('permissions')->insertGetId($permission);
            $permissionRole[] = ['permission_id' => $id, 'role_id' => 1];
        }

        DB::table('role_permissions')->insert($permissionRole);
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');

        $permissions = ['viewCurrency', 'createCurrency', 'editCurrency', 'destroyCurrency', 'updateRates'];

        foreach ($permissions as $permission) {
            DB::table('permissions')->where('code', $permission)->delete();
        }
    }
};
