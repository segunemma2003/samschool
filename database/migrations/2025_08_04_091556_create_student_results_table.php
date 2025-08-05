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
        Schema::create('student_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('term_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');

            // Calculated summary data
            $table->decimal('total_score', 10, 2)->nullable();
            $table->decimal('average_score', 8, 2)->nullable();
            $table->string('grade', 10)->nullable();
            $table->string('remarks', 50)->nullable();
            $table->integer('total_subjects')->default(0);

            // Teacher comment
            $table->text('teacher_comment')->nullable();
            $table->foreignId('commented_by')->nullable()->constrained('users')->onDelete('set null');

            // Complete result data in JSON
            $table->json('calculated_data');

            // Metadata
            $table->timestamp('calculated_at');
            $table->string('calculation_status')->default('completed'); // completed, processing, failed

            $table->timestamps();

            // Indexes for performance
            $table->index(['student_id', 'term_id', 'academic_year_id']);
            $table->index(['class_id', 'term_id', 'academic_year_id']);
            $table->index('calculated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_results');
    }
};
