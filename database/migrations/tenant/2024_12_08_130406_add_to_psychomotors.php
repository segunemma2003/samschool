<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('psychomotors', function (Blueprint $table) {
            // Modify the existing 'class_id' column to make it nullable
            $table->foreignId('class_id')->nullable()->change();

            // Add 'group_id' only if it doesn't already exist
            if (!Schema::hasColumn('psychomotors', 'group_id')) {
                $table->foreignId('group_id')->nullable()->constrained('student_groups')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('psychomotors', function (Blueprint $table) {
            //
        });
    }
};
