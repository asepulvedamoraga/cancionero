<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Repertoire;
use App\Services\PublicRepertoireAccessService;
use App\Services\RepertoirePageService;
use Illuminate\View\View;

class PublicRepertoireController extends Controller
{
    public function __invoke(Repertoire $repertoire, PublicRepertoireAccessService $access, RepertoirePageService $pages): View
    {
        $access->ensurePublic($repertoire);
        $repertoire->load('owner');
        $songs = $repertoire->songs()->where('is_active', true)->withCount(['files as page_count' => fn ($query) => $query->whereIn('file_type', ['image', 'generated_image', 'pdf'])])->get();

        return view('public.repertoires.show', [
            'repertoire' => $repertoire,
            'songs' => $songs,
            'presentationPageCount' => count($pages->pages($repertoire, true)),
        ]);
    }
}
