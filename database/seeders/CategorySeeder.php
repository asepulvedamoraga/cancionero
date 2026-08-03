<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Música litúrgica', 'Música religiosa', 'Música contemporánea', 'Otros'] as $order => $name) {
            Category::updateOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'sort_order' => $order + 1, 'is_active' => true]);
        }
    }
}
