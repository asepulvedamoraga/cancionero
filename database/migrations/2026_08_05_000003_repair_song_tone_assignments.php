<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('song_tones')) {
            return;
        }

        $now = now();

        DB::table('songs')
            ->select(['id', 'musical_key'])
            ->orderBy('id')
            ->chunkById(200, function ($songs) use ($now): void {
                foreach ($songs as $song) {
                    $songId = (int) $song->id;

                    $defaultTone = DB::table('song_tones')
                        ->where('song_id', $songId)
                        ->where('is_default', true)
                        ->first();

                    if (! $defaultTone) {
                        $firstTone = DB::table('song_tones')
                            ->where('song_id', $songId)
                            ->orderBy('id')
                            ->first();

                        if ($firstTone) {
                            DB::table('song_tones')
                                ->where('id', $firstTone->id)
                                ->update(['is_default' => true, 'updated_at' => $now]);

                            $defaultTone = $firstTone;
                        } else {
                            $name = trim((string) ($song->musical_key ?? ''));
                            if ($name === '') {
                                $name = 'Original';
                            }

                            $toneId = DB::table('song_tones')->insertGetId([
                                'song_id' => $songId,
                                'name' => $name,
                                'is_default' => true,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);

                            $defaultTone = (object) ['id' => $toneId, 'name' => $name, 'song_id' => $songId];
                        }
                    }

                    // Keep only one default tone per song.
                    DB::table('song_tones')
                        ->where('song_id', $songId)
                        ->where('id', '!=', $defaultTone->id)
                        ->where('is_default', true)
                        ->update(['is_default' => false, 'updated_at' => $now]);

                    $this->repairSongFilesForSong($songId, (int) $defaultTone->id, $now);
                    $this->repairRepertoirePivotForSong($songId, (int) $defaultTone->id, $now);
                }
            });
    }

    public function down(): void
    {
        // Data repair migration: no destructive rollback.
    }

    private function repairSongFilesForSong(int $songId, int $defaultToneId, $now): void
    {
        $rows = DB::table('song_files')
            ->leftJoin('song_tones', 'song_tones.id', '=', 'song_files.song_tone_id')
            ->where('song_files.song_id', $songId)
            ->where(function ($query): void {
                $query->whereNull('song_files.song_tone_id')
                    ->orWhereColumn('song_tones.song_id', '!=', 'song_files.song_id');
            })
            ->select(['song_files.id', 'song_files.song_tone_id', 'song_tones.name as linked_tone_name'])
            ->get();

        foreach ($rows as $row) {
            $targetToneId = $defaultToneId;

            $name = trim((string) ($row->linked_tone_name ?? ''));
            if ($name !== '') {
                $candidate = DB::table('song_tones')
                    ->where('song_id', $songId)
                    ->where('name', $name)
                    ->first();

                if (! $candidate) {
                    $candidateId = DB::table('song_tones')->insertGetId([
                        'song_id' => $songId,
                        'name' => $name,
                        'is_default' => false,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    $targetToneId = $candidateId;
                } else {
                    $targetToneId = (int) $candidate->id;
                }
            }

            DB::table('song_files')
                ->where('id', $row->id)
                ->update(['song_tone_id' => $targetToneId]);
        }
    }

    private function repairRepertoirePivotForSong(int $songId, int $defaultToneId, $now): void
    {
        $rows = DB::table('repertoire_song')
            ->leftJoin('song_tones', 'song_tones.id', '=', 'repertoire_song.song_tone_id')
            ->where('repertoire_song.song_id', $songId)
            ->where(function ($query): void {
                $query->whereNull('repertoire_song.song_tone_id')
                    ->orWhereColumn('song_tones.song_id', '!=', 'repertoire_song.song_id');
            })
            ->select([
                'repertoire_song.repertoire_id',
                'repertoire_song.song_id',
                'repertoire_song.song_tone_id',
                'song_tones.name as linked_tone_name',
            ])
            ->get();

        foreach ($rows as $row) {
            $targetToneId = $defaultToneId;

            $name = trim((string) ($row->linked_tone_name ?? ''));
            if ($name !== '') {
                $candidate = DB::table('song_tones')
                    ->where('song_id', $songId)
                    ->where('name', $name)
                    ->first();

                if (! $candidate) {
                    $candidateId = DB::table('song_tones')->insertGetId([
                        'song_id' => $songId,
                        'name' => $name,
                        'is_default' => false,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    $targetToneId = $candidateId;
                } else {
                    $targetToneId = (int) $candidate->id;
                }
            }

            DB::table('repertoire_song')
                ->where('repertoire_id', $row->repertoire_id)
                ->where('song_id', $row->song_id)
                ->update(['song_tone_id' => $targetToneId]);
        }
    }
};
