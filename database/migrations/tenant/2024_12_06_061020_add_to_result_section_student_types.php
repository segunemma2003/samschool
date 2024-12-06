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
        Schema::table('result_section_student_types', function (Blueprint $table) {
            if (!Schema::hasColumn('result_section_student_types', 'course_form_id')) {
                $table->foreignId('course_form_id')
                    ->nullable()
                    ->constrained('course_forms')
                    ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('result_section_student_types', function (Blueprint $table) {
            //
        });
    }
};
