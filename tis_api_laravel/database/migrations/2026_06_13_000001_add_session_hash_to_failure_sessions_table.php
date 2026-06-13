<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('failure_sessions', function (Blueprint $table) {
            $table->string('session_hash', 64)->nullable()->after('session_id');
            $table->unique('session_hash');
        });
    }

    public function down(): void
    {
        Schema::table('failure_sessions', function (Blueprint $table) {
            $table->dropUnique(['session_hash']);
            $table->dropColumn('session_hash');
        });
    }
};
