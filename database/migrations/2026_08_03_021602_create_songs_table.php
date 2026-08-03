<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->string('title')->index();
            $table->string('slug')->unique();
            $table->string('author')->nullable()->index();
            $table->string('performer')->nullable();
            $table->string('musical_key', 30)->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('liturgical_moment_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->string('source')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('song_liturgical_season', function (Blueprint $table) {
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->foreignId('liturgical_season_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['song_id', 'liturgical_season_id']);
            $table->index('liturgical_season_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('song_liturgical_season');
        Schema::dropIfExists('songs');
    }
};
