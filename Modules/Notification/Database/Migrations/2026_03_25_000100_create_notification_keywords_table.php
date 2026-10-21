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
        Schema::create('notification_keywords', function (Blueprint $table) {
            $table->id();

            $table->string('keyword');
            $table->string('description')->nullable();

            $table->timestamps();
        });

        $notificationKeywords = [
            [
                'keyword'     => '[$amount]',
                'description' => 'Amount',
            ],
            [
                'keyword'     => '[$description]',
                'description' => 'Description',
            ],
        ];

        DB::table('notification_keywords')->insert($notificationKeywords);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_keywords');
    }
};
