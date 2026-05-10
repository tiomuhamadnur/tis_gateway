<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('failure_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('failure_sessions')->onDelete('cascade');

            // Fields dari FailureRecord.to_dict() — sesuai Blueprint §4.1 & §6.3
            $table->unsignedInteger('block_no');
            $table->dateTime('timestamp');
            $table->unsignedTinyInteger('car_no');           // 1-6
            $table->tinyInteger('occur_recover');             // 0=Occur, 1=Recover
            $table->string('train_id', 10);                  // "FFFF" atau "0107"
            $table->integer('location_m')->default(0);       // signed [m]
            $table->unsignedTinyInteger('equipment_code');   // 1-23
            $table->string('equipment_name', 50);            // "PA", "DOOR", dll
            $table->unsignedSmallInteger('fault_code');      // numeric fault code
            $table->string('fault_abbrev', 20);              // "DATASA", "ESA", dll
            $table->text('fault_description')->nullable();
            $table->string('classification', 10);            // Heavy / Light / Info
            $table->text('guidance')->nullable();
            $table->string('notch', 10);                     // "EB", "B1", "P2", dll
            $table->unsignedSmallInteger('speed_kmh')->default(0);
            $table->unsignedSmallInteger('overhead_v')->default(0); // sudah dalam Volt (raw×10)

            $table->timestamps();

            $table->index(['session_id', 'block_no']);
            $table->index('fault_code');
            $table->index('equipment_code');
            $table->index('timestamp');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('failure_records');
    }
};
