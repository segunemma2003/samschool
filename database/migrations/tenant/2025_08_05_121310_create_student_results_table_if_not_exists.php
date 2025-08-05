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
        if (!Schema::hasTable('student_results')) {
            Schema::create('student_results', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('term_id');
                $table->unsignedBigInteger('academic_year_id');
                $table->unsignedBigInteger('class_id');

                // Calculated summary data
                $table->decimal('total_score', 10, 2)->nullable();
                $table->decimal('average_score', 8, 2)->nullable();
                $table->string('grade', 10)->nullable();
                $table->string('remarks', 50)->nullable();
                $table->integer('total_subjects')->default(0);

                // Teacher comment
                $table->text('teacher_comment')->nullable();
                $table->unsignedBigInteger('commented_by')->nullable();

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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_results');
    }
};
