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
        abort_unless($repertoire->songs()->whereKey($song->id)->exists(), 404);
    }
}
