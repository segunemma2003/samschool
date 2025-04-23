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
    // Drop foreign keys and tables if they exist
    if (Schema::hasTable('messages')) {
        try {
            Schema::table('messages', function (Blueprint $table) {
                // Try to drop foreign key safely
                $table->dropForeign(['channel_id']);
            });
        } catch (\Throwable $e) {
            // Foreign key doesn't exist, skip
        }

        try {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropForeign(['sender_id']);
            });
        } catch (\Throwable $e) {
            //
        }

        if (Schema::hasTable('message_reads')) {
            try {
                Schema::table('message_reads', function (Blueprint $table) {
                    $table->dropForeign(['message_id']);
                });
            } catch (\Throwable $e) {
                //
            }


        }

        if (Schema::hasTable('message_variables')) {
            try {
                Schema::table('message_variables', function (Blueprint $table) {
                    $table->dropForeign(['message_id']);
                });
            } catch (\Throwable $e) {
                //
            }


        }
        Schema::drop('messages');
        if (Schema::hasTable('message_reads')) {
            Schema::drop('message_reads');
        }
        if (Schema::hasTable('message_variables')) {
            Schema::drop('message_variables');
        }

    }

    // Recreate messages table
    Schema::create('messages', function (Blueprint $table) {
        $table->id();
        $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
        $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
        $table->text('body')->nullable();
        $table->boolean('is_read')->default(false);
        $table->string('attachment')->nullable();
        $table->string('attachment_type')->nullable();
        $table->timestamps();

        $table->index(['conversation_id', 'sender_id']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
