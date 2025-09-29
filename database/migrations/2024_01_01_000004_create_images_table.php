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
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('original_filename');
            $table->string('file_path');
            $table->string('file_hash', 64)->unique(); // SHA-256 hash for deduplication
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size'); // in bytes
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->json('metadata')->nullable(); // EXIF, processing results, etc.
            $table->enum('status', ['uploaded', 'processing', 'processed', 'error'])->default('uploaded');
            $table->text('processing_notes')->nullable();
            $table->timestamps();
            
            $table->index(['project_id', 'status']);
            $table->index(['uploaded_by']);
            $table->index(['file_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};