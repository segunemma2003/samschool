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
        Schema::table('users', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->default('male');
            $table->string('religion')->nullable();
            $table->date('joining_date')->nullable();
            $table->string('avatar')->nullable();
            $table->string('username')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->default('male');
            $table->string('religion')->nullable();
            $table->date('joining_date')->nullable();
            $table->string('avatar')->nullable();
            $table->string('username')->nullable();
        });
    }
};
