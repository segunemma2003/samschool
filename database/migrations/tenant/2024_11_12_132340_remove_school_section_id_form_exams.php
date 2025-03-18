<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign('exams_school_section_id_foreign');

            // Drop the column
            $table->dropColumn('school_section_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            // Add the column back
            $table->unsignedBigInteger('school_section_id')->nullable();

            // Restore the foreign key constraint
            $table->foreign('school_section_id')->references('id')->on('school_sections')->onDelete('cascade');
            // Adjust the referenced table and onDelete action as needed
        });
    }
};
