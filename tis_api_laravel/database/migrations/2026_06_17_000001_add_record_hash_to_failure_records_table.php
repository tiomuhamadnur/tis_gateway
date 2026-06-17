<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add record_hash for record-level dedup
        Schema::table('failure_records', function (Blueprint $table) {
            $table->string('record_hash', 64)->nullable()->after('block_no');
            $table->unique('record_hash', 'failure_records_record_hash_unique');
        });

        // Drop session_hash unique constraint — no longer relevant
        Schema::table('failure_sessions', function (Blueprint $table) {
            $table->dropUnique(['session_hash']);
        });

        // Drop (rake_id, read_time) unique — sessions always created now
        Schema::table('failure_sessions', function (Blueprint $table) {
            $table->dropUnique('failure_sessions_rake_read_unique');
        });
    }

    public function down(): void
    {
        Schema::table('failure_records', function (Blueprint $table) {
            $table->dropUnique('failure_records_record_hash_unique');
            $table->dropColumn('record_hash');
        });

        Schema::table('failure_sessions', function (Blueprint $table) {
            $table->unique(['rake_id', 'read_time'], 'failure_sessions_rake_read_unique');
            $table->unique('session_hash');
        });
    }
};
