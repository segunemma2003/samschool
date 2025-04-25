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
        Schema::create('hostel_meals', function (Blueprint $table) {
            $table->id();
            $table->date('meal_date');
            $table->string('meal_type'); // breakfast, lunch, dinner
            $table->string('menu_name');
            $table->text('menu_description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hostel_meals');
    }
};
