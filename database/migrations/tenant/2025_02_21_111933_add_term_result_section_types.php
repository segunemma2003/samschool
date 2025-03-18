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
        Schema::table('result_section_types', function (Blueprint $table) {
            $table->unsignedBigInteger('term_id')->nullable(); // Allow NULL if no terms exist
        });

        // Get the first available term ID
        $firstTermId = DB::table('terms')->orderBy('id')->value('id');

        // Only set default if a term exists
        if ($firstTermId) {
            DB::statement("ALTER TABLE result_section_types ALTER COLUMN term_id SET DEFAULT $firstTermId");
        }

        // Add foreign key constraint
        Schema::table('result_section_types', function (Blueprint $table) {
            $table->foreign('term_id')
                ->references('id')
                ->on('terms')
                ->onUpdate('cascade')
                ->onDelete('set null'); // Ensure referential integrity
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropColumns()
        Schema::table('result_section_types', function (Blueprint $table) {
            $table->dropForeign(['term_id']);
            $table->dropColumn('term_id');
        });
    }
};
