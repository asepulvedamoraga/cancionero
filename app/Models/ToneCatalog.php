<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ToneCatalog extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'normalized_name', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function resolveFromLabel(?string $label): self
    {
        $value = trim((string) $label);
        if ($value !== '') {
            $byName = self::query()->where('name', $value)->first();
            if ($byName) {
                return $byName;
            }

            $normalized = self::normalize($value);
            if ($normalized !== '') {
                $byAlias = DB::table('tone_catalog_aliases')
                    ->join('tone_catalogs', 'tone_catalogs.id', '=', 'tone_catalog_aliases.tone_catalog_id')
                    ->where('tone_catalog_aliases.alias_normalized', $normalized)
                    ->select('tone_catalogs.id')
                    ->first();

                if ($byAlias) {
                    return self::query()->findOrFail((int) $byAlias->id);
                }

                $byNormalizedName = self::query()->where('normalized_name', $normalized)->first();
                if ($byNormalizedName) {
                    return $byNormalizedName;
                }
            }
        }

        return self::query()->where('normalized_name', 'original')->firstOrFail();
    }

    public static function normalize(string $value): string
    {
        $normalized = mb_strtolower(trim($value));
        $normalized = strtr($normalized, [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'ä' => 'a',
            'ë' => 'e',
            'ï' => 'i',
            'ö' => 'o',
            'ü' => 'u',
        ]);
        $normalized = str_replace(['mayor', 'major'], 'maj', $normalized);
        $normalized = str_replace(['menor', 'minor'], 'min', $normalized);

        return preg_replace('/[^a-z0-9#b]+/', '', $normalized) ?? '';
    }
}
