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
        Schema::create('hostel_rule_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained();
            $table->foreignId('reported_by')->constrained('users');
            $table->string('violation_type');
            $table->text('description');
            $table->dateTime('violation_date');
            $table->string('severity'); // minor, major, critical
            $table->string('status')->default('reported'); // reported, reviewed, action_taken, resolved
            $table->text('action_taken')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hostel_rule_violations');
    }
};
