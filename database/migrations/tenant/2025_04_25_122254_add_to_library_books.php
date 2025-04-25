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
        Schema::table('library_books', function (Blueprint $table) {
            $table->foreignId('shelf_id')->nullable()->constrained('library_shelves');
            $table->integer('row_number')->nullable();
            $table->integer('position_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('library_books', function (Blueprint $table) {
            Schema::dropColumns(['shelf_id','row_number','position_number']);
        });
    }
};
