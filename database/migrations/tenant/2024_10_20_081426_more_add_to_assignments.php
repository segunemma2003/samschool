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
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->string('status')->default('available');
            $table->string('weight_mark')->default('0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->string('status')->default('available');
            $table->string('weight_mark')->default('0');
        });
    }
};
