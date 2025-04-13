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
        Schema::table('assignments', function (Blueprint $table) {
            // Drop old section_id if it exists
            if (Schema::hasColumn('assignments', 'section_id')) {
                try {
                    $table->dropForeign(['section_id']);
                } catch (\Throwable $e) {
                    // Ignore if FK doesn't exist
                }

                $table->dropColumn('section_id');
            }

            // Add new term_id column and foreign key
            if (!Schema::hasColumn('assignments', 'term_id')) {
                $table->foreignId('term_id')->constrained('terms')->onDelete('cascade');
            }

            // Add academic_id if it doesn't exist
            if (!Schema::hasColumn('assignments', 'academic_id')) {
                $table->foreignId('academic_id')->constrained('academic_years')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            // Drop term_id foreign key & column
            if (Schema::hasColumn('assignments', 'term_id')) {
                try {
                    $table->dropForeign(['term_id']);
                } catch (\Throwable $e) {}

                $table->dropColumn('term_id');
            }

            // Drop academic_id foreign key & column
            if (Schema::hasColumn('assignments', 'academic_id')) {
                try {
                    $table->dropForeign(['academic_id']);
                } catch (\Throwable $e) {}

                $table->dropColumn('academic_id');
            }

            // Restore section_id column & FK
            if (!Schema::hasColumn('assignments', 'section_id')) {
                $table->foreignId('section_id')->constrained('school_sections')->onDelete('cascade');
            }
        });
    }
};
