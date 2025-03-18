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
        Schema::create('invoice_student_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_group_id')->constrained('invoice_students')->onDelete('cascade');
            $table->decimal('amount', 30,10)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_student_details');
    }
};
