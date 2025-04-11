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
        Schema::create('arms_teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arm_id')->constrained('arms');
            $table->foreignId('teacher_id')->constrained('teachers');
            $table->foreignId('class_id')->constrained('school_classes');
            $table->foreignId('term_id')->constrained('terms');
            $table->foreignId('academic_id')->constrained('academic_years');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arms_teachers');
    }
};
