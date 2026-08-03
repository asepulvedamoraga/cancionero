<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReorderSongFilesRequest;
use App\Http\Requests\ReplaceSongFileRequest;
use App\Models\Song;
use App\Models\SongFile;
use App\Services\SongFileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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

    private function belongs(Song $song, SongFile $file): void
    {
        abort_unless($file->song_id === $song->id, 404);
    }
}
