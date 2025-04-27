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
        Schema::create('program_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fundraising_program_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->constrained('guardians')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'online']);
            $table->string('transaction_reference')->nullable();
            $table->string('payment_proof_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('is_anonymous')->default(false);
            $table->text('message')->nullable();
            $table->foreignId('school_payment_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_contributions');
    }
};
