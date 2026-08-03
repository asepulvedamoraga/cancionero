<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CatalogManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function __construct(private readonly CatalogManagementService $catalogs) {}

    public function index(string $catalog): View
    {
        return view('admin.catalogs.index', [
            'catalog' => $catalog,
            'definition' => $this->catalogs->definition($catalog),
            'items' => $this->catalogs->items($catalog),
        ]);
    }

    public function store(Request $request, string $catalog): RedirectResponse
    {
        $definition = $this->catalogs->definition($catalog);
        $this->catalogs->create($catalog, $this->validated($request, $definition['table']));

        return back()->with('status', ucfirst($definition['singular']).' creada correctamente.');
    }

    public function update(Request $request, string $catalog, int $item): RedirectResponse
    {
        $definition = $this->catalogs->definition($catalog);
        $model = $this->catalogs->find($catalog, $item);
        $this->catalogs->update($catalog, $model, $this->validated($request, $definition['table'], $model->getKey()));

        return back()->with('status', ucfirst($definition['singular']).' actualizada correctamente.');
    }

    private function validated(Request $request, string $table, ?int $ignore = null): array
    {
        $request->merge([
            'is_active' => $request->boolean('is_active'),
            'slug' => $request->filled('slug') ? $request->input('slug') : Str::slug((string) $request->input('name')),
        ]);

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique($table, 'slug')->ignore($ignore)],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
