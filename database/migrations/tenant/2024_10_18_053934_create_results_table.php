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
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->decimal('ca_one',3,2)->default(0.0);
            $table->decimal('assignment_score',3,2)->default(0.0);
            $table->decimal('ca_two',3,2)->default(0.0);
            $table->decimal('ca_three',3,2)->default(0.0);
            $table->decimal('exam',3,2)->default(0.0);
            $table->decimal('total',3,2)->default(0.0);
            $table->decimal('cummulative_score',3,2)->default(0.0);
            $table->decimal('avg',3,2)->default(0.0);
            $table->text('comment');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
