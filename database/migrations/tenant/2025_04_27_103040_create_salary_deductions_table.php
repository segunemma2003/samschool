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
        Schema::create('salary_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_structure_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('amount', 10, 2);
            $table->boolean('is_percentage')->default(false);
            $table->string('percentage_of')->nullable(); // basic, gross, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_deductions');
    }
};
