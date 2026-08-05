<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddRepertoireSongsRequest;
use App\Http\Requests\ReorderRepertoireSongsRequest;
use App\Http\Requests\UpdateRepertoireSongRequest;
use App\Models\Repertoire;
use App\Models\Song;
use App\Models\SongTone;
use App\Services\RepertoireService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class RepertoireSongController extends Controller
{
    public function store(AddRepertoireSongsRequest $request, Repertoire $repertoire, RepertoireService $service): RedirectResponse
    {
        Gate::authorize('update', $repertoire);
        $count = $service->addSongs($repertoire, $request->validated('song_ids'), (array) $request->validated('song_tones', []));

        return redirect()->route('repertoires.edit', $repertoire)->with('status', $count === 1 ? 'Canción agregada al repertorio.' : $count.' canciones agregadas al repertorio.');
    }

    public function update(UpdateRepertoireSongRequest $request, Repertoire $repertoire, Song $song): RedirectResponse
    {
        Gate::authorize('update', $repertoire);
        $this->ensureAttached($repertoire, $song);

        $toneId = $request->integer('song_tone_id') ?: null;
        if ($toneId && ! SongTone::query()->whereKey($toneId)->where('song_id', $song->id)->exists()) {
            return back()->withErrors(['song_tone_id' => 'La tonalidad seleccionada no corresponde a la canción.']);
        }

        $repertoire->songs()->updateExistingPivot($song->id, [
            'notes' => $request->string('notes')->trim()->value() ?: null,
            'song_tone_id' => $song->resolveToneId($toneId),
        ]);

        return back()->with('status', 'Nota actualizada.');
    }

    public function destroy(Repertoire $repertoire, Song $song): RedirectResponse
    {
        Gate::authorize('update', $repertoire);
        $this->ensureAttached($repertoire, $song);
        $repertoire->songs()->detach($song->id);
        $this->normalizeOrder($repertoire);

        return back()->with('status', 'Canción quitada del repertorio.');
    }

    public function reorder(ReorderRepertoireSongsRequest $request, Repertoire $repertoire, RepertoireService $service): JsonResponse
    {
        Gate::authorize('update', $repertoire);
        $service->reorder($repertoire, $request->validated('songs'));

        return response()->json(['message' => 'Orden guardado.']);
    }

    private function ensureAttached(Repertoire $repertoire, Song $song): void
    {
        abort_unless($repertoire->songs()->whereKey($song->id)->exists(), 404);
    }

    private function normalizeOrder(Repertoire $repertoire): void
    {
        foreach ($repertoire->songs()->pluck('songs.id') as $index => $songId) {
            $repertoire->songs()->updateExistingPivot($songId, ['sort_order' => $index + 1]);
        }
    }
}
