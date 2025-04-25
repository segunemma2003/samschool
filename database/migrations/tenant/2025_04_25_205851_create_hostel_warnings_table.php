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
        Schema::create('hostel_warnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained();
            $table->foreignId('issued_by')->constrained('users');
            $table->foreignId('rule_violation_id')->nullable()->constrained('hostel_rule_violations');
            $table->string('warning_type');
            $table->text('description');
            $table->date('issue_date');
            $table->date('valid_until');
            $table->string('status')->default('active'); // active, expired, rescinded
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hostel_warnings');
    }
};
