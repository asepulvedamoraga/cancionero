<?php

namespace App\Http\Controllers;

use App\Models\Song;
use App\Models\SongTone;
use App\Models\ToneCatalog;
use App\Services\SongFileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SongToneController extends Controller
{
    public function store(Request $request, Song $song, SongFileService $files): RedirectResponse
    {
        Gate::authorize('update', $song);

        $request->merge([
            'tone_catalog_id' => $request->integer('tone_catalog_id') ?: null,
        ]);

        $max = (int) config('cancionero.upload_max_mb', 20) * 1024;

        $validated = $request->validate([
            'tone_catalog_id' => [
                'required',
                'integer',
                Rule::exists('tone_catalogs', 'id')->where('is_active', true),
                Rule::unique('song_tones', 'tone_catalog_id')->where('song_id', $song->id),
            ],
            'files' => ['nullable', 'array', 'max:30'],
            'files.*' => ['file', "max:{$max}", 'mimetypes:image/jpeg,image/png,image/webp,application/pdf', 'extensions:jpg,jpeg,png,webp,pdf'],
        ], [
            'tone_catalog_id.required' => 'Selecciona una tonalidad.',
            'tone_catalog_id.unique' => 'Esa tonalidad ya existe en esta canción.',
        ]);

        [$tone, $warnings] = DB::transaction(function () use ($song, $validated, $request, $files): array {
            $catalog = ToneCatalog::query()->findOrFail((int) $validated['tone_catalog_id']);

            $tone = $song->tones()->create([
                'tone_catalog_id' => $catalog->id,
                'name' => $catalog->name,
                'is_default' => false,
            ]);

            $warnings = [];
            $uploaded = $request->file('files', []);

            if ($uploaded !== []) {
                $result = $files->storeUploads($song, $uploaded, (int) $tone->id);
                $warnings = $result['warnings'];
            }

            return [$tone, $warnings];
        });

        return redirect()
            ->route('songs.edit', ['song' => $song, 'tone' => $tone->id])
            ->with('status', 'Tonalidad agregada correctamente.')
            ->with('warnings', $warnings);
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

    public function destroy(Song $song, SongTone $tone, SongFileService $files): RedirectResponse
    {
        Gate::authorize('update', $song);
        $this->ensureBelongs($song, $tone);

        if (DB::table('repertoire_song')->where('song_id', $song->id)->exists()) {
            return back()->withErrors(['tones' => 'No puedes eliminar tonalidades porque la canción está siendo utilizada en uno o más repertorios.']);
        }

        if ($song->tones()->count() <= 1) {
            return back()->withErrors(['tones' => 'No puedes eliminar la única tonalidad de la canción.']);
        }

        DB::transaction(function () use ($song, $tone, $files): void {
            $replacement = $song->tones()
                ->whereKeyNot($tone->id)
                ->orderByDesc('is_default')
                ->orderBy('id')
                ->firstOrFail();

            $toneFiles = $tone->files()->get();
            foreach ($toneFiles as $toneFile) {
                $files->delete($toneFile);
            }

            if ($tone->is_default) {
                $song->tones()->update(['is_default' => false]);
                $replacement->update(['is_default' => true]);
            }

            $tone->delete();
        });

        return back()->with('status', 'Tonalidad eliminada junto con sus archivos asociados.');
    }

    private function ensureBelongs(Song $song, SongTone $tone): void
    {
        abort_unless($tone->song_id === $song->id, 404);
    }
}
