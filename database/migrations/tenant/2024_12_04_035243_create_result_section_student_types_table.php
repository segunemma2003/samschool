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
        Schema::create('result_section_student_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_section_type_id')->nullable()->constrained('result_section_types')->onDelete('cascade');
            $table->foreignId('course_form_id')->constrained('course_forms')->onDelete('cascade');
            $table->decimal('score',3,2)->default(0.0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('result_section_student_types');
    }
};
