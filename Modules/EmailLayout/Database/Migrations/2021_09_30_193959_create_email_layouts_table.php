<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailLayoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_layouts', function (Blueprint $table) {
            $table->id();
            $table->text('subject');
            $table->text('email');
            $table->text('signature')->nullable();
            $table->string('attach')->nullable();
            $table->string('attach_name')->nullable();
            $table->foreignId('email_type_id')
                ->constrained()
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->timestamps();
        });

        $permissions = [
            ['name' => 'Visualizar Layout de Email', 'key' => 'viewEmailLayout', 'category' => 'Layouts de Emails'],
            ['name' => 'Editar Layout de Email', 'key' => 'editEmailLayout', 'category' => 'Layouts de Emails'],
        ];
        $permissionRole = array();

        foreach ($permissions as $permission) {
            $id = DB::table('permissions')->insertGetId($permission);
            array_push($permissionRole, ['permission_id' => $id, 'role_id' => 1]);
        }
        DB::table('permission_role')->insert($permissionRole);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_layouts');
    }
}
