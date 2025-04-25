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
        Schema::create('hostel_damage_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_inventory_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('hostel_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reported_by')->constrained('users');
            $table->foreignId('student_id')->nullable()->constrained();
            $table->string('damage_type');
            $table->text('description');
            $table->string('severity'); // minor, moderate, severe
            $table->decimal('repair_cost', 10, 2)->nullable();
            $table->string('status')->default('reported'); // reported, assessed, repaired, written_off
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hostel_damage_reports');
    }
};
