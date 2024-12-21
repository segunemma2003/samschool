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
        Schema::create('invoice_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('term_id')->nullable()->constrained('terms')->onDelete('cascade');
            $table->foreignId('academic_id')->nullable()->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->decimal('total_amount', 30,10)->default(0.00);
            $table->decimal('amount_paid', 30,10)->default(0.00);
            $table->decimal('amount_owed', 30,10)->default(0.00);
            $table->string('order_code');
            $table->string('status')->default('owing');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_students');
    }
};
