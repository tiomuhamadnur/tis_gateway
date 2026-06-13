<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('uploaded_files', function (Blueprint $table) {
            $table->string('session_id')->nullable()->after('file_id');
            $table->index('session_id');
            $table->unique(['session_id', 'original_filename']);
        });
    }

    public function down(): void
    {
        Schema::table('uploaded_files', function (Blueprint $table) {
            $table->dropUnique(['session_id', 'original_filename']);
            $table->dropIndex(['session_id']);
            $table->dropColumn('session_id');
        });
    }
};
