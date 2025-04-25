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
        Schema::create('online_library_materials', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('type_id')->constrained('online_library_types');
            $table->text('description');
            $table->string('author');
            $table->string('publisher')->nullable();
            $table->year('publication_year')->nullable();
            $table->string('isbn')->nullable();
            $table->string('doi')->nullable(); // Digital Object Identifier
            $table->string('cover_image')->nullable();
            $table->string('file_path'); // Path to the actual file
            $table->string('file_type'); // pdf, epub, etc.
            $table->integer('view_count')->default(0);
            $table->integer('download_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_library_materials');
    }
};
