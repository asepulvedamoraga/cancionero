<?php

namespace Database\Seeders;

use App\Models\LiturgicalSeason;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LiturgicalSeasonSeeder extends Seeder
{
    public function run(): void
    {
        $items = ['Tiempo Ordinario', 'Adviento', 'Navidad', 'Cuaresma', 'Semana Santa', 'Pascua', 'Pentecostés', 'Mariano', 'General'];
        foreach ($items as $order => $name) {
            LiturgicalSeason::updateOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'sort_order' => $order + 1, 'is_active' => true]);
        }
    }
}
