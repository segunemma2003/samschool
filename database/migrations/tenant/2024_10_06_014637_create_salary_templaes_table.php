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
        Schema::create('salary_templaes', function (Blueprint $table) {
            $table->id();
            $table->decimal('basic',15,2)->default(0.00);
            $table->decimal('bonus',15,2)->default(0.00);
            $table->decimal('total',15,2)->default(0.00);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_templaes');
    }
};
