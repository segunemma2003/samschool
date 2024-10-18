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
        Schema::create('my_events', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('details');
            $table->string('link')->nullable();
            $table->string('file')->nullable();
            $table->string('date_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('my_events');
    }
};
