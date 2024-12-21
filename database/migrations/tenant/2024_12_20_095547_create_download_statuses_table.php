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
        Schema::create('download_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('processing');
            $table->string('download_links')->nullable();
            $table->string('time');
            $table->string('data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('download_statuses');
    }
};
