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
        Schema::create('hostel_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hostel_room_id')->constrained();
            $table->foreignId('student_id')->constrained();
            $table->foreignId('term_id')->constrained('terms');
            $table->foreignId('academic_id')->constrained('academic_years');
            $table->date('assignment_date');
            $table->date('release_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hostel_assignments');
    }
};
