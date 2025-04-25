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
        Schema::create('hostel_meal_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_meal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained();
            $table->boolean('attended')->default(false);
            $table->text('special_requirements')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hostel_meal_attendances');
    }
};
