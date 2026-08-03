<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\LiturgicalMoment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SongFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return ['user_id' => User::factory(), 'title' => $title, 'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999), 'author' => fake()->optional()->name(), 'category_id' => Category::factory(), 'liturgical_moment_id' => LiturgicalMoment::factory(), 'is_active' => true];
    }
}
