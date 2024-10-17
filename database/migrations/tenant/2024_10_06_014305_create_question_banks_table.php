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
        Schema::create('question_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');
            $table->text('question');  // The actual question
            $table->enum('question_type', ['multiple_choice', 'open_ended', 'true_false']);  // Type of question
            $table->text('answer')->nullable();  // Correct answer (nullable for open-ended)
            $table->json('options')->nullable();  // Options for multiple choice questions stored as JSON
            $table->text('hint')->nullable();  // Hint for the question (nullable)
            $table->integer('marks');  // Marks assigned to the question
            $table->string('image')->nullable();  // Path to an image if required (nullable)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_banks');
    }
};
