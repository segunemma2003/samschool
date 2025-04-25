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
        Schema::create('hostel_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_room_id')->constrained()->cascadeOnDelete();
            $table->string('item_name');
            $table->string('item_type'); // bed, mattress, furniture, etc.
            $table->string('serial_number')->nullable();
            $table->date('purchase_date');
            $table->decimal('purchase_cost', 10, 2);
            $table->string('condition'); // new, good, fair, poor, damaged
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hostel_inventories');
    }
};
