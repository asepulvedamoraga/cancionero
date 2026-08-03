<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRepertoireRequest;
use App\Http\Requests\UpdateRepertoireRequest;
use App\Models\Category;
use App\Models\LiturgicalMoment;
use App\Models\LiturgicalSeason;
use App\Models\Repertoire;
use App\Models\Song;
use App\Services\RepertoirePageService;
use App\Services\RepertoireService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RepertoireController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Repertoire::class);
        $query = Repertoire::query()
            ->when(! $request->user()->is_admin, fn ($query) => $query->where(fn ($visible) => $visible->where('user_id', $request->user()->id)->orWhere('visibility', 'public')))
            ->with('owner')->withCount('songs')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($sub) => $sub->where('name', 'like', '%'.$request->string('q').'%')->orWhere('location', 'like', '%'.$request->string('q').'%')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('event_type'), fn ($q) => $q->where('event_type', 'like', '%'.$request->string('event_type').'%'));

        match ($request->string('sort')->toString()) {
            'name' => $query->orderBy('name'),
            'oldest' => $query->oldest('event_date')->oldest(),
            'newest' => $query->latest('event_date')->latest(),
            default => $query->orderByRaw('event_date IS NULL')->orderBy('event_date')->orderBy('event_time'),
        };

        $trashedCount = Repertoire::onlyTrashed()
            ->when(! $request->user()->is_admin, fn ($archived) => $archived->where('user_id', $request->user()->id))
            ->count();

        return view('repertoires.index', [
            'repertoires' => $query->paginate(12)->withQueryString(),
            'trashedCount' => $trashedCount,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Repertoire::class);

        return view('repertoires.create');
    }

    public function store(StoreRepertoireRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;
        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?? null, $validated['name']);
        $repertoire = Repertoire::create($validated);

        return redirect()->route('repertoires.edit', $repertoire)->with('status', 'Repertorio creado. Ahora puedes agregar canciones.');
    }

    public function show(Repertoire $repertoire, RepertoirePageService $pages): View
    {
        Gate::authorize('view', $repertoire);
        $repertoire->load(['owner', 'songs.category', 'songs.liturgicalMoment'])->loadCount('songs');
        $repertoire->songs->loadCount(['files as page_count' => fn ($query) => $query->whereIn('file_type', ['image', 'generated_image', 'pdf'])]);

        return view('repertoires.show', ['repertoire' => $repertoire, 'presentationPageCount' => count($pages->pages($repertoire))]);
    }

    public function edit(Request $request, Repertoire $repertoire): View
    {
        Gate::authorize('update', $repertoire);
        $repertoire->load(['songs.owner', 'songs.category', 'songs.liturgicalMoment']);
        $repertoire->songs->loadCount(['files as page_count' => fn ($query) => $query->whereIn('file_type', ['image', 'generated_image', 'pdf'])]);

        $searchTerms = preg_split('/\s+/', trim($request->string('song_q')->toString()), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $selectedSongIds = $repertoire->songs->modelKeys();

        $songs = Song::query()->where('is_active', true)->whereNotIn('id', $selectedSongIds)
            ->with(['owner', 'category', 'liturgicalMoment'])->withCount(['files as page_count' => fn ($query) => $query->whereIn('file_type', ['image', 'generated_image', 'pdf'])])
            ->when($searchTerms, function ($query) use ($searchTerms): void {
                foreach ($searchTerms as $term) {
                    $query->where(fn ($sub) => $sub->where('title', 'like', '%'.$term.'%')
                        ->orWhere('author', 'like', '%'.$term.'%')
                        ->orWhere('performer', 'like', '%'.$term.'%'));
                }
            })
            ->when($request->integer('category_id'), fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->integer('liturgical_moment_id'), fn ($q, $id) => $q->where('liturgical_moment_id', $id))
            ->when($request->integer('liturgical_season_id'), fn ($q, $id) => $q->whereHas('liturgicalSeasons', fn ($seasons) => $seasons->whereKey($id)))
            ->orderBy('title')->paginate(12)->withQueryString();

        return view('repertoires.edit', [
            'repertoire' => $repertoire,
            'songs' => $songs,
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(),
            'moments' => LiturgicalMoment::where('is_active', true)->orderBy('sort_order')->get(),
            'seasons' => LiturgicalSeason::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function update(UpdateRepertoireRequest $request, Repertoire $repertoire): RedirectResponse
    {
        Gate::authorize('update', $repertoire);
        $validated = $request->validated();
        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?? null, $validated['name'], $repertoire->id);
        $repertoire->update($validated);

        return redirect()->route('repertoires.edit', $repertoire)->with('status', 'Repertorio actualizado correctamente.');
    }

    public function destroy(Repertoire $repertoire): RedirectResponse
    {
        Gate::authorize('delete', $repertoire);

        DB::transaction(function () use ($repertoire): void {
            if ($repertoire->visibility === 'public') {
                $repertoire->update(['visibility' => 'private', 'allow_public_download' => false]);
            }

            $repertoire->delete();
        });

        return redirect()->route('repertoires.index')->with('status', 'Repertorio enviado a la papelera.');
    }

    public function trashed(Request $request): View
    {
        Gate::authorize('viewAny', Repertoire::class);

        $query = Repertoire::onlyTrashed()
            ->when(! $request->user()->is_admin, fn ($repertoires) => $repertoires->where('user_id', $request->user()->id))
            ->with('owner')->withCount('songs')
            ->when($request->filled('q'), fn ($repertoires) => $repertoires->where(fn ($search) => $search
                ->where('name', 'like', '%'.$request->string('q').'%')
                ->orWhere('location', 'like', '%'.$request->string('q').'%')))
            ->latest('deleted_at');

        return view('repertoires.trashed', ['repertoires' => $query->paginate(12)->withQueryString()]);
    }

    public function restore(int $repertoire): RedirectResponse
    {
        $repertoire = Repertoire::onlyTrashed()->findOrFail($repertoire);
        Gate::authorize('restore', $repertoire);
        $repertoire->restore();

        return redirect()->route('repertoires.show', $repertoire)->with('status', 'Repertorio restaurado como privado.');
    }

    public function duplicate(Repertoire $repertoire, RepertoireService $service): RedirectResponse
    {
        Gate::authorize('duplicate', $repertoire);
        $repertoire->load('songs');
        $name = $repertoire->name.' - Copia';
        $copy = $service->duplicate($repertoire, $name, $this->uniqueSlug(null, $name));

        return redirect()->route('repertoires.edit', $copy)->with('status', 'Repertorio duplicado como borrador.');
    }

    private function uniqueSlug(?string $slug, string $name, ?int $ignore = null): string
    {
        $base = Str::slug($slug ?: $name) ?: Str::random(8);
        $candidate = $base;
        $i = 2;
        while (Repertoire::withTrashed()->where('slug', $candidate)->when($ignore, fn ($q) => $q->whereKeyNot($ignore))->exists()) {
            $candidate = $base.'-'.$i++;
        }

        return $candidate;
    }
}
