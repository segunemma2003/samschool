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
        Schema::create('school_information', function (Blueprint $table) {
            $table->id();
            $table->foreignId('term_id')->nullable()->constrained('terms')->onDelete('cascade');
            $table->foreignId('academic_id')->nullable()->constrained('academic_years')->onDelete('cascade');
            $table->string('school_name');
            $table->string('school_address');
            $table->string('school_principal_name');
            $table->text('principal_sign');
            $table->text('school_stamp');
            $table->string('school_phone');
            $table->string('school_website');
            $table->text('school_logo');
            $table->string('term_begin');
            $table->string('term_ends');
            $table->string('next_term_begins')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_information');
    }
};
