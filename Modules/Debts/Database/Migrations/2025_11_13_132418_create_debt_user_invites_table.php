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
        Schema::create('debt_user_invites', function (Blueprint $table) {
            $table->unsignedBigInteger("debt_id");
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("shared_role_id");
            $table->unsignedBigInteger('invited_by_id');
            $table->enum("status", ["pending", "revoked", "accepted"])->default("pending");
            $table->timestamps();

            $table->foreign("debt_id")->references("id")->on("debts")->onDelete("cascade");
            $table->foreign("user_id")->references("id")->on("users")->onDelete("cascade");
            $table->foreign('invited_by_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign("shared_role_id")->references("id")->on("shared_roles")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debt_user_invites');
    }
};
