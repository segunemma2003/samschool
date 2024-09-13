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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('password');
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->default('male');
            $table->string('blood_group')->nullable();
            $table->string('religion')->nullable();
            $table->date('joining_date')->nullable();
            $table->string('avatar')->nullable();
            $table->string('username')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('optional_subject')->nullable();
            $table->string('registration_number')->unique();
            $table->string('roll')->nullable();
            $table->text('extra')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('class_id')->constrained('school_classes')->onDelete('cascade');
            $table->foreignId('guardian_id')->constrained('guardians')->onDelete('cascade');
            $table->foreignId('section_id')->constrained('school_sections')->onDelete('cascade');
            $table->foreignId('group_id')->constrained('student_groups')->onDelete('cascade');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
