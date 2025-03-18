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
            if (!Schema::hasColumn('result_section_student_types', 'result_section_type_id')) {
                $table->foreignId('result_section_type_id')
                    ->nullable()
                    ->constrained('result_section_types')
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
