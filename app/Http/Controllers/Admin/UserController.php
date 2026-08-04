<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->withCount(['songs', 'repertoires'])
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($search) => $search->where('name', 'like', '%'.$request->string('q').'%')->orWhere('email', 'like', '%'.$request->string('q').'%')))
            ->latest()
            ->paginate($this->perPage($request))
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    private function perPage(Request $request): int
    {
        $perPage = $request->integer('per_page', 12);

        return in_array($perPage, [12, 24, 48], true) ? $perPage : 12;
    }
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate(['is_active' => ['required', 'boolean']]);
        $active = (bool) $validated['is_active'];

        if ($request->user()->is($user) && ! $active) {
            return back()->withErrors(['user' => 'No puedes desactivar tu propia cuenta.']);
        }

        $user->update(['is_active' => $active]);

        return back()->with('status', $active ? 'Cuenta activada correctamente.' : 'Cuenta desactivada correctamente.');
    }
}
