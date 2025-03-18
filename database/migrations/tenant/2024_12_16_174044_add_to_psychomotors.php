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
        Schema::table('psychomotors', function (Blueprint $table) {
            $table->foreignId('psychomotor_category_id')->nullable()->constrained('psychomotor_categories')->onDelete('cascade');
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
