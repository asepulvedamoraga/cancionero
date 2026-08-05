<?php

namespace App\Http\Controllers;

use App\Models\Song;
use App\Models\SongTone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SongToneController extends Controller
{
    public function store(Request $request, Song $song): RedirectResponse
    {
        Gate::authorize('update', $song);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:60', Rule::unique('song_tones', 'name')->where('song_id', $song->id)],
        ], [
            'name.required' => 'Ingresa un nombre de tonalidad.',
            'name.unique' => 'Esa tonalidad ya existe en esta canción.',
        ]);

        $song->tones()->create([
            'name' => trim($validated['name']),
            'is_default' => false,
        ]);

        return back()->with('status', 'Tonalidad agregada.');
    }

    public function makeDefault(Song $song, SongTone $tone): RedirectResponse
    {
        Gate::authorize('update', $song);
        $this->ensureBelongs($song, $tone);

        DB::transaction(function () use ($song, $tone): void {
            $song->tones()->update(['is_default' => false]);
            $tone->update(['is_default' => true]);
        });

        return back()->with('status', 'Tonalidad predeterminada actualizada.');
    }

    public function destroy(Song $song, SongTone $tone): RedirectResponse
    {
        Gate::authorize('update', $song);
        $this->ensureBelongs($song, $tone);

        if ($song->tones()->count() <= 1) {
            return back()->withErrors(['tones' => 'No puedes eliminar la única tonalidad de la canción.']);
        }

        DB::transaction(function () use ($song, $tone): void {
            $replacement = $song->tones()
                ->whereKeyNot($tone->id)
                ->orderByDesc('is_default')
                ->orderBy('id')
                ->firstOrFail();

            DB::table('song_files')
                ->where('song_id', $song->id)
                ->where('song_tone_id', $tone->id)
                ->update(['song_tone_id' => $replacement->id]);

            DB::table('repertoire_song')
                ->where('song_id', $song->id)
                ->where('song_tone_id', $tone->id)
                ->update(['song_tone_id' => $replacement->id]);

            if ($tone->is_default) {
                $song->tones()->update(['is_default' => false]);
                $replacement->update(['is_default' => true]);
            }

            $tone->delete();
        });

        return back()->with('status', 'Tonalidad eliminada y archivos reasignados.');
    }

    private function ensureBelongs(Song $song, SongTone $tone): void
    {
        abort_unless($tone->song_id === $song->id, 404);
    }
}
