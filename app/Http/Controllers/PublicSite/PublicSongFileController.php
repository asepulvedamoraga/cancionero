<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Song;
use App\Models\SongFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicSongFileController extends Controller
{
    public function show(Song $song, SongFile $file): StreamedResponse
    {
        abort_unless($song->is_active, 404);
        abort_unless($file->song_id === $song->id, 404);
        abort_unless(in_array($file->file_type, ['image', 'generated_image', 'pdf'], true), 404);
        abort_unless(Storage::disk('local')->exists($file->original_path), 404);

        $publicName = 'cancion-'.$song->slug.'-'.$file->id.'.'.$file->extension;

        return Storage::disk('local')->response($file->original_path, $publicName, [
            'Content-Type' => $file->mime_type,
            'Content-Disposition' => 'inline; filename="'.$publicName.'"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
