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
        Schema::create('student_attendance_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained('students')->onDelete('cascade');
            $table->foreignId('term_id')->nullable()->constrained('terms')->onDelete('cascade');
            $table->foreignId('academic_id')->nullable()->constrained('academic_years')->onDelete('cascade');
            $table->string('total_present')->default("0");
            $table->string('total_absent')->default("0");
            $table->string('expected_present')->default("0");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_attendance_summaries');
    }
};