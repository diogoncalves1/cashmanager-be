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
        Schema::create('notification_type_keywords', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('notification_type_id');
            $table->unsignedBigInteger('notification_keyword_id');

            $table->timestamps();

            $table->foreign('notification_type_id')->references('id')->on('notification_types')->onDelete('cascade');
            $table->foreign('notification_keyword_id')->references('id')->on('notification_keywords')->onDelete('cascade');
        });

        $notificationKeywords = [
            [
                'notification_type_id'    => 1,
                'notification_keyword_id' => 1,
            ],
            [
                'notification_type_id'    => 1,
                'notification_keyword_id' => 2,
            ],
        ];

        DB::table('notification_type_keywords')->insert($notificationKeywords);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_type_keywords');
    }
};
