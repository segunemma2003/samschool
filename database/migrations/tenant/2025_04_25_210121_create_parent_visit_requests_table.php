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
        Schema::create('parent_visit_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('guardians');
            $table->foreignId('student_id')->constrained();
            $table->foreignId('hostel_building_id')->constrained();
            $table->dateTime('proposed_visit_date');
            $table->string('purpose');
            $table->string('status')->default('pending'); // pending, approved, rejected, completed
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('actual_visit_date')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_visit_requests');
    }
};
