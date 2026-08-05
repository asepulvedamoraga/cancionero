<?php

namespace App\Http\Controllers;

use App\Models\Repertoire;
use App\Models\Song;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $songs = Song::query()->when(! $request->user()->is_admin, fn (Builder $query) => $query->where(fn (Builder $visible) => $visible->where('is_active', true)->orWhere('user_id', $request->user()->id)));
        $repertoires = Repertoire::query()->when(! $request->user()->is_admin, fn (Builder $query) => $query->where(fn (Builder $visible) => $visible->where('user_id', $request->user()->id)->orWhere('visibility', 'public')));

        return view('dashboard', [
            'songCount' => (clone $songs)->count(),
            'repertoireCount' => (clone $repertoires)->count(),
            'latestSongs' => (clone $songs)->latest()->limit(5)->get(),
            'recentRepertoires' => (clone $repertoires)->latest()->limit(5)->get(),
            'lastRepertoire' => (clone $repertoires)->latest()->first(),
        ]);
    }
}
