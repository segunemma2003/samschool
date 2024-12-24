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
            $table->dropForeign(['invoice_group_id']); // Drop the old foreign key
            $table->foreign('invoice_group_id')
                  ->references('id')
                  ->on('invoice_groups') // Reference the correct table
                  ->onDelete('cascade'); // Add cascade delete if needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
