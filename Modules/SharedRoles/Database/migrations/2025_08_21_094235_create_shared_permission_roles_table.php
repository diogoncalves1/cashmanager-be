<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shared_permission_roles', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('shared_role_id');
            $table->unsignedBigInteger('shared_permission_id');

            $table->timestamps();

            $table->foreign('shared_permission_id')->references('id')->on('shared_permissions')->onDelete('cascade');
            $table->foreign('shared_role_id')->references('id')->on('shared_roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shared_permission_roles');
    }
};
