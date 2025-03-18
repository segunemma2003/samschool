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
        Schema::table('invoice_student_details', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_student_details', 'student_id')) {
                $table->dropForeign(['student_id']);

                // Drop the column
                $table->dropColumn('student_id');
                }
                if (!Schema::hasColumn('invoice_student_details', 'invoice_student_id')) {
                $table->foreignId('invoice_student_id')
                      ->constrained('invoice_students')
                      ->onDelete('cascade');
                }

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_student_details', function (Blueprint $table) {
            //
        });
    }
};
