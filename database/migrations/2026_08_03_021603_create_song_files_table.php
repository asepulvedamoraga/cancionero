<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('song_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('original_path');
            $table->string('preview_path')->nullable();
            $table->string('mime_type', 100);
            $table->string('extension', 20);
            $table->enum('file_type', ['image', 'pdf', 'generated_image'])->index();
            $table->unsignedBigInteger('file_size');
            $table->unsignedInteger('page_number')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_generated')->default(false);
            $table->timestamps();
            $table->index(['song_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('song_files');
    }
};
