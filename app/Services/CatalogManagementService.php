<?php

namespace App\Services;

use App\Models\Category;
use App\Models\LiturgicalMoment;
use App\Models\LiturgicalSeason;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CatalogManagementService
{
    private const CATALOGS = [
        'categories' => ['model' => Category::class, 'table' => 'categories', 'title' => 'Categorías', 'singular' => 'categoría', 'description' => 'Organiza las canciones por su uso o temática.'],
        'moments' => ['model' => LiturgicalMoment::class, 'table' => 'liturgical_moments', 'title' => 'Momentos litúrgicos', 'singular' => 'momento litúrgico', 'description' => 'Define el momento de la celebración en que se utiliza una canción.'],
        'seasons' => ['model' => LiturgicalSeason::class, 'table' => 'liturgical_seasons', 'title' => 'Tiempos litúrgicos', 'singular' => 'tiempo litúrgico', 'description' => 'Relaciona las canciones con uno o más tiempos del calendario litúrgico.'],
    ];

    public function definition(string $catalog): array
    {
        abort_unless(isset(self::CATALOGS[$catalog]), 404);

        return self::CATALOGS[$catalog];
    }

    public function items(string $catalog)
    {
        $definition = $this->definition($catalog);

        return $definition['model']::query()->withCount('songs')->orderBy('sort_order')->orderBy('name')->get();
    }

    public function find(string $catalog, int $id): Model
    {
        $definition = $this->definition($catalog);

        return $definition['model']::query()->findOrFail($id);
    }

    public function create(string $catalog, array $data): Model
    {
        $definition = $this->definition($catalog);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

        return $definition['model']::query()->create($data);
    }

    public function update(string $catalog, Model $item, array $data): void
    {
        $definition = $this->definition($catalog);

        if ($item->is_active && ! $data['is_active'] && $item->songs()->exists()) {
            throw ValidationException::withMessages([
                'is_active' => 'No puedes desactivar este '.$definition['singular'].' porque está asociado a una o más canciones.',
            ]);
        }

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $item->update($data);
    }
}
