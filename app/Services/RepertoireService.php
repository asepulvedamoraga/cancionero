<?php

namespace App\Services;

use App\Models\Repertoire;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RepertoireService
{
    public function addSongs(Repertoire $repertoire, array $songIds): int
    {
        $songIds = collect($songIds)->map(fn ($id) => (int) $id)->unique()->values();
        $existing = $repertoire->songs()->whereKey($songIds)->pluck('songs.id');

        if ($existing->isNotEmpty()) {
            throw ValidationException::withMessages(['song_ids' => 'Una o más canciones ya pertenecen al repertorio.']);
        }

        return DB::transaction(function () use ($repertoire, $songIds): int {
            $nextOrder = ((int) $repertoire->songs()->max('repertoire_song.sort_order')) + 1;
            $attachments = [];
            foreach ($songIds as $songId) {
                $attachments[$songId] = ['sort_order' => $nextOrder++];
            }
            $repertoire->songs()->attach($attachments);

            return count($attachments);
        });
    }

    public function reorder(Repertoire $repertoire, array $songIds): void
    {
        $currentIds = $repertoire->songs()->pluck('songs.id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $requestedIds = collect($songIds)->map(fn ($id) => (int) $id)->sort()->values()->all();

        if ($currentIds !== $requestedIds) {
            throw ValidationException::withMessages(['songs' => 'El orden debe incluir exactamente las canciones del repertorio.']);
        }

        DB::transaction(function () use ($repertoire, $songIds): void {
            foreach ($songIds as $index => $songId) {
                $repertoire->songs()->updateExistingPivot($songId, ['sort_order' => $index + 1]);
            }
        });
    }

    public function duplicate(Repertoire $source, string $name, string $slug): Repertoire
    {
        return DB::transaction(function () use ($source, $name, $slug): Repertoire {
            $copy = $source->replicate();
            $copy->name = $name;
            $copy->slug = $slug;
            $copy->status = 'draft';
            $copy->visibility = 'private';
            $copy->allow_public_download = false;
            $copy->save();

            foreach ($source->songs as $song) {
                $copy->songs()->attach($song->id, [
                    'sort_order' => $song->pivot->sort_order,
                    'notes' => $song->pivot->notes,
                ]);
            }

            return $copy;
        });
    }
}
