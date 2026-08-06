<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tone_catalogs', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 60);
            $table->string('normalized_name', 80)->unique();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('tone_catalog_aliases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tone_catalog_id')->constrained('tone_catalogs')->cascadeOnDelete();
            $table->string('alias', 80);
            $table->string('alias_normalized', 80)->unique();
            $table->timestamps();
        });

        $this->seedCatalog();

        Schema::table('song_tones', function (Blueprint $table): void {
            $table->unsignedBigInteger('tone_catalog_id')->nullable()->after('song_id');
            $table->index('tone_catalog_id');
        });

        $this->backfillSongTones();
        $this->mergeDuplicateTones();
        $this->syncSongsMusicalKey();

        DB::statement('ALTER TABLE song_tones MODIFY tone_catalog_id BIGINT UNSIGNED NOT NULL');

        Schema::table('song_tones', function (Blueprint $table): void {
            $table->foreign('tone_catalog_id')->references('id')->on('tone_catalogs')->restrictOnDelete();
        });

        $this->dropSongToneNameUniqueIndex();

        Schema::table('song_tones', function (Blueprint $table): void {
            $table->unique(['song_id', 'tone_catalog_id'], 'song_tones_song_catalog_unique');
        });
    }

    public function down(): void
    {
        try {
            Schema::table('song_tones', function (Blueprint $table): void {
                $table->dropUnique('song_tones_song_catalog_unique');
            });
        } catch (\Throwable) {
        }

        try {
            Schema::table('song_tones', function (Blueprint $table): void {
                $table->dropForeign(['tone_catalog_id']);
            });
        } catch (\Throwable) {
        }

        try {
            Schema::table('song_tones', function (Blueprint $table): void {
                $table->dropIndex(['tone_catalog_id']);
                $table->dropColumn('tone_catalog_id');
            });
        } catch (\Throwable) {
        }

        try {
            Schema::table('song_tones', function (Blueprint $table): void {
                $table->unique(['song_id', 'name']);
            });
        } catch (\Throwable) {
        }

        Schema::dropIfExists('tone_catalog_aliases');
        Schema::dropIfExists('tone_catalogs');
    }

    private function seedCatalog(): void
    {
        $now = now();
        $insertedAliases = [];

        foreach ($this->definitions() as $order => $definition) {
            $catalogId = DB::table('tone_catalogs')->insertGetId([
                'name' => $definition['name'],
                'normalized_name' => $this->normalize($definition['name']),
                'sort_order' => $order + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $aliases = array_unique(array_merge([$definition['name']], $definition['aliases']));
            foreach ($aliases as $alias) {
                $normalizedAlias = $this->normalize($alias);
                if ($normalizedAlias === '' || isset($insertedAliases[$normalizedAlias])) {
                    continue;
                }

                DB::table('tone_catalog_aliases')->insert([
                    'tone_catalog_id' => $catalogId,
                    'alias' => $alias,
                    'alias_normalized' => $normalizedAlias,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $insertedAliases[$normalizedAlias] = true;
            }
        }
    }

    private function backfillSongTones(): void
    {
        DB::table('song_tones')
            ->select(['id', 'name'])
            ->orderBy('id')
            ->chunkById(200, function ($tones): void {
                foreach ($tones as $tone) {
                    $rawName = trim((string) $tone->name);
                    if ($rawName === '') {
                        $rawName = 'Original';
                    }

                    [$catalogId, $catalogName] = $this->resolveCatalog($rawName);

                    DB::table('song_tones')
                        ->where('id', $tone->id)
                        ->update([
                            'tone_catalog_id' => $catalogId,
                            'name' => $catalogName,
                        ]);
                }
            });
    }

    private function mergeDuplicateTones(): void
    {
        $duplicates = DB::table('song_tones')
            ->select('song_id', 'tone_catalog_id', DB::raw('COUNT(*) as total'))
            ->groupBy('song_id', 'tone_catalog_id')
            ->having('total', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            $group = DB::table('song_tones')
                ->where('song_id', $duplicate->song_id)
                ->where('tone_catalog_id', $duplicate->tone_catalog_id)
                ->orderByDesc('is_default')
                ->orderBy('id')
                ->get();

            $keeper = $group->first();
            if (! $keeper) {
                continue;
            }

            $duplicateIds = $group->pluck('id')->filter(fn ($id) => (int) $id !== (int) $keeper->id)->values()->all();
            if ($duplicateIds === []) {
                continue;
            }

            DB::table('song_files')
                ->whereIn('song_tone_id', $duplicateIds)
                ->update(['song_tone_id' => $keeper->id]);

            DB::table('repertoire_song')
                ->whereIn('song_tone_id', $duplicateIds)
                ->update(['song_tone_id' => $keeper->id]);

            $hasDefaultInGroup = collect($group)->contains(fn ($tone) => (bool) $tone->is_default);
            if ($hasDefaultInGroup) {
                DB::table('song_tones')->where('id', $keeper->id)->update(['is_default' => true]);
            }

            DB::table('song_tones')->whereIn('id', $duplicateIds)->delete();
        }
    }

    private function syncSongsMusicalKey(): void
    {
        DB::table('songs')
            ->select('id')
            ->orderBy('id')
            ->chunkById(200, function ($songs): void {
                foreach ($songs as $song) {
                    $defaultTone = DB::table('song_tones')
                        ->where('song_id', $song->id)
                        ->orderByDesc('is_default')
                        ->orderBy('id')
                        ->first();

                    if (! $defaultTone) {
                        continue;
                    }

                    DB::table('songs')
                        ->where('id', $song->id)
                        ->update(['musical_key' => $defaultTone->name]);
                }
            });
    }

    private function dropSongToneNameUniqueIndex(): void
    {
        $index = DB::table('information_schema.statistics')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', 'song_tones')
            ->where('index_name', 'song_tones_song_id_name_unique')
            ->exists();

        if ($index) {
            DB::statement('ALTER TABLE song_tones DROP INDEX song_tones_song_id_name_unique');
        }
    }

    private function resolveCatalog(string $rawName): array
    {
        $normalized = $this->normalize($rawName);

        $catalog = DB::table('tone_catalog_aliases')
            ->join('tone_catalogs', 'tone_catalogs.id', '=', 'tone_catalog_aliases.tone_catalog_id')
            ->where('tone_catalog_aliases.alias_normalized', $normalized)
            ->select('tone_catalogs.id', 'tone_catalogs.name')
            ->first();

        if ($catalog) {
            return [(int) $catalog->id, (string) $catalog->name];
        }

        $catalogByNormalized = DB::table('tone_catalogs')
            ->where('normalized_name', $normalized)
            ->first();

        if ($catalogByNormalized) {
            return [(int) $catalogByNormalized->id, (string) $catalogByNormalized->name];
        }

        $now = now();
        $catalogId = DB::table('tone_catalogs')->insertGetId([
            'name' => $rawName,
            'normalized_name' => $this->uniqueNormalizedName($normalized),
            'sort_order' => (int) DB::table('tone_catalogs')->max('sort_order') + 1,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('tone_catalog_aliases')->insert([
            'tone_catalog_id' => $catalogId,
            'alias' => $rawName,
            'alias_normalized' => $this->normalize($rawName),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [$catalogId, $rawName];
    }

    private function uniqueNormalizedName(string $normalized): string
    {
        $base = $normalized !== '' ? $normalized : 'tone';
        $candidate = $base;
        $i = 2;

        while (DB::table('tone_catalogs')->where('normalized_name', $candidate)->exists()) {
            $candidate = $base.'-'.$i;
            $i++;
        }

        return $candidate;
    }

    private function normalize(string $value): string
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

    private function definitions(): array
    {
        return [
            ['name' => 'Original', 'aliases' => ['original', 'orig', 'sin tono']],
            ['name' => 'Do mayor (C)', 'aliases' => ['do', 'c', 'do mayor', 'c major']],
            ['name' => 'Do# mayor (C#)', 'aliases' => ['do#', 'c#', 'do sostenido', 'c sharp']],
            ['name' => 'Reb mayor (Db)', 'aliases' => ['reb', 'db', 're bemol']],
            ['name' => 'Re mayor (D)', 'aliases' => ['re', 'd', 're mayor', 'd major']],
            ['name' => 'Mib mayor (Eb)', 'aliases' => ['mib', 'eb', 'mi bemol']],
            ['name' => 'Mi mayor (E)', 'aliases' => ['mi', 'e', 'mi mayor', 'e major']],
            ['name' => 'Fa mayor (F)', 'aliases' => ['fa', 'f', 'fa mayor', 'f major']],
            ['name' => 'Fa# mayor (F#)', 'aliases' => ['fa#', 'f#', 'fa sostenido', 'f sharp']],
            ['name' => 'Solb mayor (Gb)', 'aliases' => ['solb', 'gb', 'sol bemol']],
            ['name' => 'Sol mayor (G)', 'aliases' => ['sol', 'g', 'sol mayor', 'g major']],
            ['name' => 'Lab mayor (Ab)', 'aliases' => ['lab', 'ab', 'la bemol']],
            ['name' => 'La mayor (A)', 'aliases' => ['la', 'a', 'la mayor', 'a major']],
            ['name' => 'Sib mayor (Bb)', 'aliases' => ['sib', 'bb', 'si bemol', 'b bemol']],
            ['name' => 'Si mayor (B)', 'aliases' => ['si', 'b', 'si mayor', 'b major']],
            ['name' => 'Do menor (Cm)', 'aliases' => ['dom', 'cm', 'do menor', 'c minor']],
            ['name' => 'Do# menor (C#m)', 'aliases' => ['do#m', 'c#m', 'do sostenido menor', 'c sharp minor']],
            ['name' => 'Re menor (Dm)', 'aliases' => ['rem', 'dm', 're menor', 'd minor']],
            ['name' => 'Mib menor (Ebm)', 'aliases' => ['mibm', 'ebm', 'mi bemol menor']],
            ['name' => 'Mi menor (Em)', 'aliases' => ['mim', 'em', 'mi menor', 'e minor']],
            ['name' => 'Fa menor (Fm)', 'aliases' => ['fam', 'fm', 'fa menor', 'f minor']],
            ['name' => 'Fa# menor (F#m)', 'aliases' => ['fa#m', 'f#m', 'fa sostenido menor', 'f sharp minor']],
            ['name' => 'Sol menor (Gm)', 'aliases' => ['solm', 'gm', 'sol menor', 'g minor']],
            ['name' => 'Lab menor (Abm)', 'aliases' => ['labm', 'abm', 'la bemol menor']],
            ['name' => 'La menor (Am)', 'aliases' => ['lam', 'am', 'la menor', 'a minor']],
            ['name' => 'Sib menor (Bbm)', 'aliases' => ['sibm', 'bbm', 'si bemol menor']],
            ['name' => 'Si menor (Bm)', 'aliases' => ['sim', 'bm', 'si menor', 'b minor']],
        ];
    }
};
