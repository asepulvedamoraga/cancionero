<?php

namespace App\Services;

use App\Models\Repertoire;
use App\Models\Song;
use App\Models\SongFile;

class PublicRepertoireAccessService
{
    public function ensurePublic(Repertoire $repertoire): void
    {
        abort_unless($repertoire->visibility === 'public', 404);
    }

    public function ensureFileIsPublic(Repertoire $repertoire, Song $song, SongFile $file): void
    {
        $this->ensurePublic($repertoire);
        abort_unless($file->song_id === $song->id, 404);
        abort_unless($song->is_active, 404);

        $pivot = $repertoire->songs()
            ->whereKey($song->id)
            ->select('repertoire_song.song_tone_id')
            ->first()?->pivot;

        abort_unless($pivot, 404);

        $defaultToneId = $song->tones()->where('is_default', true)->value('id');
        $expectedToneId = (int) ($pivot->song_tone_id ?: $defaultToneId);

        if ($expectedToneId > 0) {
            abort_unless((int) $file->song_tone_id === $expectedToneId, 404);
        }
    }
}
