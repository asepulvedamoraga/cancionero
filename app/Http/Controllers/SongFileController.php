<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReorderSongFilesRequest;
use App\Http\Requests\ReplaceSongFileRequest;
use App\Models\Song;
use App\Models\SongFile;
use App\Models\SongTone;
use App\Services\SongFileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SongFileController extends Controller
{
    public function show(Song $song, SongFile $file): StreamedResponse
    {
        Gate::authorize('view', $song);
        $this->belongs($song, $file);
        abort_unless(Storage::disk('local')->exists($file->original_path), 404);

        return Storage::disk('local')->response($file->original_path, $file->original_name, ['Content-Type' => $file->mime_type, 'Content-Disposition' => 'inline; filename="'.addslashes($file->original_name).'"']);
    }

    public function preview(Song $song, SongFile $file): StreamedResponse
    {
        Gate::authorize('view', $song);
        $this->belongs($song, $file);
        $path = $file->preview_path ?: $file->original_path;
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }

    public function download(Song $song, SongFile $file): StreamedResponse
    {
        Gate::authorize('view', $song);
        $this->belongs($song, $file);
        abort_unless(Storage::disk('local')->exists($file->original_path), 404);

        return Storage::disk('local')->download($file->original_path, $file->original_name);
    }

    public function destroy(Song $song, SongFile $file, SongFileService $service): RedirectResponse
    {
        Gate::authorize('update', $song);
        $this->belongs($song, $file);

        if ($this->songUsedInRepertoires($song)) {
            return back()->withErrors([
                'files' => 'No puedes eliminar archivos porque la canción está siendo utilizada en uno o más repertorios.',
            ]);
        }

        $service->delete($file);

        return back()->with('status', 'Archivo eliminado correctamente.');
    }

    public function replace(ReplaceSongFileRequest $request, Song $song, SongFile $file, SongFileService $service): RedirectResponse
    {
        Gate::authorize('update', $song);
        $this->belongs($song, $file);
        $result = $service->replace($file, $request->file('file'));

        return back()->with('status', 'Archivo reemplazado correctamente.')->with('warnings', $result['warnings']);
    }

    public function reorder(ReorderSongFilesRequest $request, Song $song, SongFileService $service): JsonResponse
    {
        Gate::authorize('update', $song);
        $service->reorder($song, array_map('intval', $request->validated('files')));

        return response()->json(['message' => 'Orden guardado.']);
    }

    public function storeForTone(Request $request, Song $song, SongTone $tone, SongFileService $service): RedirectResponse
    {
        Gate::authorize('update', $song);
        abort_unless($tone->song_id === $song->id, 404);

        $max = (int) config('cancionero.upload_max_mb', 20) * 1024;
        $validated = $request->validate([
            'files' => ['required', 'array', 'min:1', 'max:30'],
            'files.*' => ['file', "max:{$max}", 'mimetypes:image/jpeg,image/png,image/webp,application/pdf', 'extensions:jpg,jpeg,png,webp,pdf'],
        ]);

        $result = $service->storeUploads($song, $validated['files'], (int) $tone->id);

        return redirect()
            ->route('songs.edit', ['song' => $song, 'tone' => $tone->id])
            ->with('status', 'Archivos agregados a la tonalidad seleccionada.')
            ->with('warnings', $result['warnings']);
    }

    private function belongs(Song $song, SongFile $file): void
    {
        abort_unless($file->song_id === $song->id, 404);
    }

    private function songUsedInRepertoires(Song $song): bool
    {
        return DB::table('repertoire_song')->where('song_id', $song->id)->exists();
    }
}
