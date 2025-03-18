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
            if (!Schema::hasColumn('result_section_student_types', 'score')) {
                $table->decimal('score', 10, 2)->default(0)->nullable();
            } else {
                $table->decimal('score', 10, 2)->default(0)->change();
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
