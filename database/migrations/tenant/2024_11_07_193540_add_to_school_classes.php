<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add the `group_id` column first (without a foreign key)
        Schema::table('school_classes', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable();  // Temporarily add the column
        });

        // Step 2: Clean up orphaned records
        DB::table('school_classes')
            ->whereNotIn('group_id', function ($query) {
                $query->select('id')->from('student_groups');
            })
            ->update(['group_id' => null]);  // You can also delete or set a valid value here

        // Step 3: Add the foreign key constraint after cleanup
        Schema::table('school_classes', function (Blueprint $table) {
            $table->foreign('group_id')->references('id')->on('student_groups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
        });
    }
};
