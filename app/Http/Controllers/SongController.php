<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSongRequest;
use App\Http\Requests\UpdateSongRequest;
use App\Models\Category;
use App\Models\LiturgicalMoment;
use App\Models\LiturgicalSeason;
use App\Models\Song;
use App\Services\PdfConversionService;
use App\Services\SongFileService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SongController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Song::class);
        $scope = $request->string('scope')->toString() === 'mine' ? 'mine' : 'library';
        $query = Song::query()
            ->when($scope === 'mine', fn ($query) => $query->where('user_id', $request->user()->id))
            ->when($scope === 'library', fn ($query) => $query->where('is_active', true))
            ->when($scope === 'library' && $request->string('ownership')->toString() === 'mine', fn ($query) => $query->where('user_id', $request->user()->id))
            ->when($scope === 'library' && $request->string('ownership')->toString() === 'others', fn ($query) => $query->where('user_id', '!=', $request->user()->id))
            ->with(['owner', 'category', 'liturgicalMoment', 'files' => fn ($q) => $q->whereIn('file_type', ['image', 'generated_image'])->orderBy('sort_order')]);
        $this->applyFilters($query, $request);

        return view('songs.index', [
            'songs' => $query->paginate($this->perPage($request))->withQueryString(),
            'scope' => $scope,
            'ownCount' => Song::where('user_id', $request->user()->id)->count(),
            'archivedCount' => Song::onlyTrashed()->when(! $request->user()->is_admin, fn ($archived) => $archived->where('user_id', $request->user()->id))->count(),
            ...$this->catalogs(),
        ]);
    }

    public function archived(Request $request): View
    {
        Gate::authorize('viewAny', Song::class);
        $query = Song::onlyTrashed()
            ->when(! $request->user()->is_admin, fn ($query) => $query->where('user_id', $request->user()->id))
            ->with(['owner', 'category'])
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($search) => $search->where('title', 'like', '%'.$request->string('q').'%')->orWhere('author', 'like', '%'.$request->string('q').'%')))
            ->latest('deleted_at');

        return view('songs.archived', ['songs' => $query->paginate($this->perPage($request))->withQueryString()]);
    }

    public function suggestions(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Song::class);
        $term = trim($request->string('q')->toString());
        if (mb_strlen($term) < 3) {
            return response()->json(['songs' => []]);
        }

        $songs = Song::query()->where('is_active', true)
            ->where(fn ($query) => $query->where('title', 'like', '%'.$term.'%')->orWhere('author', 'like', '%'.$term.'%'))
            ->with('owner:id,name')->orderBy('title')->limit(6)->get()
            ->map(fn (Song $song) => [
                'title' => $song->title,
                'author' => $song->author ?: 'Autor no indicado',
                'owner' => $song->owner->name,
                'mine' => $song->user_id === $request->user()->id,
                'url' => route('songs.show', $song),
            ]);

        return response()->json(['songs' => $songs]);
    }

    public function create(): View
    {
        Gate::authorize('create', Song::class);

        return view('songs.create', [...$this->catalogs(), 'imagickAvailable' => app(PdfConversionService::class)->available()]);
    }

    public function store(StoreSongRequest $request, SongFileService $files): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;
        $uploads = $request->file('files', []);
        $selectedToneId = $request->integer('song_tone_id') ?: null;
        unset($validated['files'], $validated['liturgical_seasons']);
        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?? null, $validated['title']);
        $result = DB::transaction(function () use ($validated, $request, $uploads, $files, $selectedToneId) {
            $song = Song::create($validated);
            $song->liturgicalSeasons()->sync($request->input('liturgical_seasons', []));
            $stored = $files->storeUploads($song, $uploads, $selectedToneId);

            return [$song, $stored];
        });

        return redirect()->route('songs.show', $result[0])->with('status', 'Canción creada correctamente.')->with('warnings', $result[1]['warnings']);
    }

    public function show(Request $request, Song $song): View
    {
        Gate::authorize('view', $song);
        $song->load(['owner', 'category', 'liturgicalMoment', 'liturgicalSeasons', 'files', 'tones']);

        $selectedTone = $song->selectedTone($request->integer('tone') ?: null);
        $displayFiles = $song->filesForTone($selectedTone->id);

        return view('songs.show', ['song' => $song, 'selectedTone' => $selectedTone, 'displayFiles' => $displayFiles]);
    }

    public function read(Request $request, Song $song): View
    {
        Gate::authorize('view', $song);
        $song->load(['files' => fn ($query) => $query->whereIn('file_type', ['image', 'generated_image'])->orderBy('sort_order'), 'tones']);

        $selectedTone = $song->selectedTone($request->integer('tone') ?: null);
        $displayFiles = $song->filesForTone($selectedTone->id)->whereIn('file_type', ['image', 'generated_image'])->values();

        return view('songs.read', compact('song', 'selectedTone', 'displayFiles'));
    }

    public function edit(Request $request, Song $song): View
    {
        Gate::authorize('update', $song);
        $song->load(['liturgicalSeasons', 'files', 'tones']);
        $selectedTone = $song->selectedTone($request->integer('tone') ?: null);

        return view('songs.edit', ['song' => $song, 'selectedTone' => $selectedTone, ...$this->catalogs(), 'imagickAvailable' => app(PdfConversionService::class)->available()]);
    }

    public function update(UpdateSongRequest $request, Song $song, SongFileService $files): RedirectResponse
    {
        Gate::authorize('update', $song);
        $validated = $request->validated();
        $uploads = $request->file('files', []);
        $selectedToneId = $request->integer('song_tone_id') ?: null;
        unset($validated['files'], $validated['liturgical_seasons']);
        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?? null, $validated['title'], $song->id);
        $stored = DB::transaction(function () use ($song, $validated, $request, $uploads, $files, $selectedToneId) {
            $song->update($validated);
            $song->liturgicalSeasons()->sync($request->input('liturgical_seasons', []));

            return $files->storeUploads($song, $uploads, $selectedToneId);
        });

        $tone = $song->resolveToneId($selectedToneId);

        return redirect()->route('songs.edit', ['song' => $song, 'tone' => $tone])->with('status', 'Canción actualizada correctamente.')->with('warnings', $stored['warnings']);
    }

    public function destroy(Song $song): RedirectResponse
    {
        Gate::authorize('delete', $song);
        $song->delete();

        return redirect()->route('songs.index', ['scope' => 'mine'])->with('status', 'Canción archivada correctamente. Sus archivos se conservaron.');
    }

    public function restore(Request $request, int $song): RedirectResponse
    {
        $archivedSong = Song::onlyTrashed()->findOrFail($song);
        Gate::authorize('restore', $archivedSong);
        $archivedSong->restore();

        return redirect()->route('songs.show', $archivedSong)->with('status', 'Canción restaurada correctamente.');
    }

    public function forceDestroy(int $song, SongFileService $files): RedirectResponse
    {
        $archivedSong = Song::onlyTrashed()->findOrFail($song);
        Gate::authorize('delete', $archivedSong);
        $files->purgeSong($archivedSong);

        return redirect()->route('songs.archived')->with('status', 'Canción eliminada definitivamente junto con sus archivos.');
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        $query->when($request->filled('q'), fn ($q) => $q->where(fn ($sub) => $sub->where('title', 'like', '%'.$request->string('q').'%')->orWhere('author', 'like', '%'.$request->string('q').'%')))
            ->when($request->integer('category_id'), fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->integer('liturgical_moment_id'), fn ($q, $id) => $q->where('liturgical_moment_id', $id))
            ->when($request->integer('liturgical_season_id'), fn ($q, $id) => $q->whereHas('liturgicalSeasons', fn ($seasons) => $seasons->whereKey($id)))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->string('status')->toString() === 'active'));
        match ($request->string('sort')->toString()) {
            'title_desc' => $query->orderByDesc('title'), 'oldest' => $query->oldest(), 'newest' => $query->latest(), default => $query->orderBy('title')
        };
    }

    private function perPage(Request $request): int
    {
        $perPage = $request->integer('per_page', 12);

        return in_array($perPage, [12, 24, 48], true) ? $perPage : 12;
    }

    private function catalogs(): array
    {
        return ['categories' => Category::where('is_active', true)->orderBy('sort_order')->get(), 'moments' => LiturgicalMoment::where('is_active', true)->orderBy('sort_order')->get(), 'seasons' => LiturgicalSeason::where('is_active', true)->orderBy('sort_order')->get()];
    }

    private function uniqueSlug(?string $slug, string $title, ?int $ignore = null): string
    {
        $base = Str::slug($slug ?: $title) ?: Str::random(8);
        $candidate = $base;
        $i = 2;
        while (Song::withTrashed()->where('slug', $candidate)->when($ignore, fn ($q) => $q->whereKeyNot($ignore))->exists()) {
            $candidate = $base.'-'.$i++;
        }

        return $candidate;
    }
}
