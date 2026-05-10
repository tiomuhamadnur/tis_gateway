<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('failure_records', function (Blueprint $table) {
            // ID record pasangan (Occur → Recover atau sebaliknya)
            $table->unsignedBigInteger('paired_record_id')->nullable()->after('occur_recover');
            // Durasi fault dalam detik (null = belum di-pair / masih aktif)
            $table->unsignedInteger('duration_seconds')->nullable()->after('paired_record_id');

            $table->foreign('paired_record_id')
                  ->references('id')
                  ->on('failure_records')
                  ->nullOnDelete();

            $table->index('paired_record_id');
        });
    }

    public function down(): void
    {
        Schema::table('failure_records', function (Blueprint $table) {
            $table->dropForeign(['paired_record_id']);
            $table->dropIndex(['paired_record_id']);
            $table->dropColumn(['paired_record_id', 'duration_seconds']);
        });
    }
};
