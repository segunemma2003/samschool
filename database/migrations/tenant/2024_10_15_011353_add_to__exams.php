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
        Schema::table('exams', function (Blueprint $table) {
            $table->text('instructions')->nullable();  // Instructions for the assessment
            $table->integer('duration')->default(60);  // Duration in minutes (default to 60 minutes)
            $table->integer('total_score')->default(0);
            $table->string('assessment_type')->default('test 1');  // Type of assessment (e.g., 'exam', 'test', 'quiz')
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->text('instructions')->nullable();  // Instructions for the assessment
            $table->integer('duration')->default(60);  // Duration in minutes (default to 60 minutes)
            $table->integer('total_score')->default(0);
            $table->string('assessment_type')->default('test 1');  // Type of assessment (e.g., 'exam', 'test', 'quiz')
        });
    }
};
