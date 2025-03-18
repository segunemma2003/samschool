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
        Schema::create('result_section_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_section_id')->constrained('result_sections')->onDelete('cascade');
            $table->string('name');
            $table->string('code');
            $table->string('type')->default('numeric');
            $table->decimal('score_weight', 3,1)->default(0.0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('result_section_types');
    }
};
