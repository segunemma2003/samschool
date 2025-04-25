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
        Schema::create('hostel_visitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_building_id')->constrained();
            $table->foreignId('student_id')->constrained();
            $table->string('visitor_name');
            $table->string('visitor_relation');
            $table->string('visitor_phone');
            $table->string('visitor_id_type');
            $table->string('visitor_id_number');
            $table->dateTime('visit_date');
            $table->dateTime('expected_departure');
            $table->dateTime('actual_departure')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected, completed
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->text('purpose');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hostel_visitors');
    }
};
