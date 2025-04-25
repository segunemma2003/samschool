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
        Schema::create('online_library_material_subject', function (Blueprint $table) {

            $table->foreignId('material_id')->constrained('online_library_materials')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('online_library_subjects')->cascadeOnDelete();
            $table->primary(['material_id', 'subject_id']); // Composite primary key
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_library_material_subject');
    }
};
