<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Repertoire;
use App\Services\PublicRepertoireAccessService;
use App\Services\RepertoirePageService;
use Illuminate\View\View;

class PublicPresentationController extends Controller
{
    public function __invoke(Repertoire $repertoire, PublicRepertoireAccessService $access, RepertoirePageService $pages): View
    {
        $access->ensurePublic($repertoire);

        return view('repertoires.presentation', [
            'repertoire' => $repertoire,
            'pages' => $pages->pages($repertoire, true),
            'exitUrl' => route('public.repertoires.show', ['repertoire' => $repertoire->slug]),
        ]);
    }
}
