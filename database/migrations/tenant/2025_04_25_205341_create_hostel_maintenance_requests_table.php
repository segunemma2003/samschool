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
        Schema::create('hostel_maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_inventory_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('hostel_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reported_by')->constrained('users');
            $table->string('issue_type');
            $table->text('description');
            $table->string('priority'); // low, medium, high, critical
            $table->string('status')->default('pending'); // pending, in_progress, completed, rejected
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->dateTime('completed_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hostel_maintenance_requests');
    }
};
