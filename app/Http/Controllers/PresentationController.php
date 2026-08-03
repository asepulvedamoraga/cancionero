<?php

namespace App\Http\Controllers;

use App\Models\Repertoire;
use App\Services\RepertoirePageService;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PresentationController extends Controller
{
    public function __invoke(Repertoire $repertoire, RepertoirePageService $pages): View
    {
        Gate::authorize('view', $repertoire);
        $repertoire->load('songs.files');

        return view('repertoires.presentation', [
            'repertoire' => $repertoire,
            'pages' => $pages->pages($repertoire),
            'exitUrl' => route('repertoires.show', $repertoire),
        ]);
    }
}
