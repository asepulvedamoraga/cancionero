<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicSongController extends Controller
{
    public function show(Request $request, Song $song): View
    {
        $this->ensureSongIsPubliclyAvailable($song);

        $song->ensureDefaultTone();
        $song->load([
            'owner:id,name',
            'category:id,name',
            'liturgicalMoment:id,name',
            'liturgicalSeasons:id,name',
            'tones:id,song_id,name,is_default',
            'files' => fn ($query) => $query->whereIn('file_type', ['image', 'generated_image', 'pdf'])->orderBy('sort_order'),
        ]);

        $selectedTone = $song->selectedTone($request->integer('tone') ?: null);
        $displayFiles = $song->filesForTone($selectedTone->id)->whereIn('file_type', ['image', 'generated_image', 'pdf'])->values();
        $publicRepertoires = $song->repertoires()->where('visibility', 'public')->select('repertoires.id', 'repertoires.name', 'repertoires.slug')->get();

        return view('public.songs.show', compact('song', 'selectedTone', 'displayFiles', 'publicRepertoires'));
    }

    public function read(Request $request, Song $song): View
    {
        $this->ensureSongIsPubliclyAvailable($song);

        $song->ensureDefaultTone();
        $song->load([
            'tones:id,song_id,name,is_default',
            'files' => fn ($query) => $query->whereIn('file_type', ['image', 'generated_image'])->orderBy('sort_order'),
        ]);

        $selectedTone = $song->selectedTone($request->integer('tone') ?: null);
        $displayFiles = $song->filesForTone($selectedTone->id)->whereIn('file_type', ['image', 'generated_image'])->values();

        return view('public.songs.read', compact('song', 'selectedTone', 'displayFiles'));
    }

    private function ensureSongIsPubliclyAvailable(Song $song): void
    {
        abort_unless($song->is_active, 404);
    }
}
