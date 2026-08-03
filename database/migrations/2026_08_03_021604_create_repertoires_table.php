<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repertoires', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('slug')->unique();
            $table->string('event_type')->nullable()->index();
            $table->date('event_date')->nullable()->index();
            $table->time('event_time')->nullable();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'ready', 'archived'])->default('draft')->index();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('repertoire_song', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repertoire_id')->constrained()->cascadeOnDelete();
            $table->foreignId('song_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['repertoire_id', 'song_id']);
            $table->index(['repertoire_id', 'sort_order']);
            $table->index('song_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repertoire_song');
        Schema::dropIfExists('repertoires');
    }
};
