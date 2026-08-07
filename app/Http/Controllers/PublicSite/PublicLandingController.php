<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Repertoire;
use App\Models\Song;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PublicLandingController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if ($request->user()?->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $search = trim((string) $request->input('search'));

        $publicRepertoires = Repertoire::query()
            ->where('visibility', 'public')
            ->with('owner:id,name')
            ->withCount(['songs as songs_count' => fn ($query) => $query->where('is_active', true)])
            ->latest('updated_at')
            ->limit(8)
            ->get();

        $publicSongsQuery = Song::query()
            ->with([
                'owner:id,name',
                'tones:id,song_id,is_default',
                'files' => fn ($query) => $query->whereIn('file_type', ['image', 'generated_image', 'pdf'])->orderBy('sort_order'),
                'repertoires' => fn ($query) => $query->where('visibility', 'public')
                    ->select('repertoires.id', 'repertoires.name', 'repertoires.slug', 'repertoires.allow_public_download')
                    ->withPivot('song_tone_id')
                    ->latest('repertoires.updated_at'),
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('author', 'like', "%{$search}%")
                        ->orWhere('performer', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhere('source', 'like', "%{$search}%");
                });
            });

        $publicSongsCount = (clone $publicSongsQuery)->count();

        $publicSongs = $publicSongsQuery
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        $publicSongs->setCollection($publicSongs->getCollection()->map(fn (Song $song): array => $this->mapPublicSong($song))->values());

        $latestSongs = (clone $publicSongsQuery)
            ->latest('created_at')
            ->limit(4)
            ->get()
            ->map(fn (Song $song): array => $this->mapPublicSong($song))
            ->values();

        return view('public.landing', [
            'publicRepertoires' => $publicRepertoires,
            'publicSongs' => $publicSongs,
            'latestSongs' => $latestSongs,
            'search' => $search,
            'stats' => [
                'repertoires' => $publicRepertoires->count(),
                'songs' => $publicSongsCount,
                'contributors' => $this->contributorsFrom($publicRepertoires),
            ],
        ]);
    }

    private function contributorsFrom(Collection $publicRepertoires): int
    {
        return (int) $publicRepertoires->pluck('owner.id')->filter()->unique()->count();
    }

    private function mapPublicSong(Song $song): array
    {
        $repertoire = $song->repertoires->first();
        $selectedToneId = (int) optional($song->tones->firstWhere('is_default', true))->id;
        if ($selectedToneId === 0) {
            $selectedToneId = (int) optional($song->tones->first())->id;
        }

        $toneFiles = $song->files
            ->where('song_tone_id', $selectedToneId)
            ->whereIn('file_type', ['image', 'generated_image', 'pdf'])
            ->values();

        $imageFile = $toneFiles->first(fn ($file) => in_array($file->file_type, ['image', 'generated_image'], true));

        return [
            'song' => $song,
            'repertoire' => $repertoire,
            'imageFile' => $imageFile,
            'files' => $toneFiles,
            'totalFiles' => $toneFiles->count(),
        ];
    }
}
