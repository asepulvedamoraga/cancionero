<?php

namespace Database\Seeders;

use App\Models\LiturgicalMoment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LiturgicalMomentSeeder extends Seeder
{
    public function run(): void
    {
        $items = ['Entrada', 'Señor, ten piedad', 'Gloria', 'Salmo', 'Aleluya', 'Ofertorio', 'Santo', 'Cordero de Dios', 'Comunión', 'Meditación', 'Acción de gracias', 'Salida', 'Otro'];
        foreach ($items as $order => $name) {
            LiturgicalMoment::updateOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'sort_order' => $order + 1, 'is_active' => true]);
        }
    }
}
