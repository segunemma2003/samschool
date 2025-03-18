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
        Schema::create('take_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('question_banks')->onDelete('cascade');
            $table->string('status')->default("not_marked");
            $table->string('approval')->default("true");
            $table->text('answer');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('take_exams');
    }
};
