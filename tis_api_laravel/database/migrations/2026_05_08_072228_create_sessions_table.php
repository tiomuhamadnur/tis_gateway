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
        Schema::create('failure_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->string('rake_id');
            $table->dateTime('read_time');            // waktu PTU membaca data dari TIS
            $table->dateTime('download_date');        // waktu server menerima upload
            $table->integer('total_records')->default(0);
            $table->string('status', 20)->default('pending'); // pending, completed, failed
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('rake_id');
            $table->index('read_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failure_sessions');
    }
};
