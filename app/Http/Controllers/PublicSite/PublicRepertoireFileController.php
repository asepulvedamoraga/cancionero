<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Repertoire;
use App\Models\Song;
use App\Models\SongFile;
use App\Services\PublicRepertoireAccessService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicRepertoireFileController extends Controller
{
    public function __invoke(Repertoire $repertoire, Song $song, SongFile $file, PublicRepertoireAccessService $access): StreamedResponse
    {
        $access->ensureFileIsPublic($repertoire, $song, $file);
        abort_unless(Storage::disk('local')->exists($file->original_path), 404);
        $publicName = 'documento-'.$file->id.'.'.$file->extension;

        return Storage::disk('local')->response($file->original_path, $publicName, [
            'Content-Type' => $file->mime_type,
            'Content-Disposition' => 'inline; filename="'.$publicName.'"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
