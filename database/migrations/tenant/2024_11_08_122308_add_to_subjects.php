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
        Schema::table('subjects', function (Blueprint $table) {
            // Add nullable foreign key for subject_depot_id referencing subject_depots table
            $table->foreignId('subject_depot_id')->nullable()->constrained('subject_depots')->onDelete('set null');
            $table->string('name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            // Drop the foreign key and column
            $table->dropForeign(['subject_depot_id']);
            $table->dropColumn('subject_depot_id');
            $table->string('name')->nullable(false)->change();
        });
    }
};
