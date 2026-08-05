<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('song_tones', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->string('name', 60);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->unique(['song_id', 'name']);
            $table->index(['song_id', 'is_default']);
        });

        Schema::table('song_files', function (Blueprint $table): void {
            $table->foreignId('song_tone_id')->nullable()->after('song_id')->constrained('song_tones')->nullOnDelete();
            $table->index(['song_id', 'song_tone_id', 'sort_order'], 'song_files_song_tone_sort_idx');
        });

        Schema::table('repertoire_song', function (Blueprint $table): void {
            $table->foreignId('song_tone_id')->nullable()->after('song_id')->constrained('song_tones')->nullOnDelete();
            $table->index('song_tone_id');
        });

        $now = now();
        $toneBySong = [];

        DB::table('songs')
            ->select(['id', 'musical_key'])
            ->orderBy('id')
            ->chunkById(200, function ($songs) use (&$toneBySong, $now): void {
                foreach ($songs as $song) {
                    $name = trim((string) ($song->musical_key ?? ''));
                    if ($name === '') {
                        $name = 'Original';
                    }

                    $toneId = DB::table('song_tones')->insertGetId([
                        'song_id' => $song->id,
                        'name' => $name,
                        'is_default' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    $toneBySong[(int) $song->id] = $toneId;
                }
            });

        foreach ($toneBySong as $songId => $toneId) {
            DB::table('song_files')
                ->where('song_id', $songId)
                ->whereNull('song_tone_id')
                ->update(['song_tone_id' => $toneId]);

            DB::table('repertoire_song')
                ->where('song_id', $songId)
                ->whereNull('song_tone_id')
                ->update(['song_tone_id' => $toneId]);
        }
    }

    public function down(): void
    {
        Schema::table('repertoire_song', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('song_tone_id');
        });

        Schema::table('song_files', function (Blueprint $table): void {
            $table->dropIndex('song_files_song_tone_sort_idx');
            $table->dropConstrainedForeignId('song_tone_id');
        });

        Schema::dropIfExists('song_tones');
    }
};
