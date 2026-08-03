<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RepertoireFactory extends Factory
{
    public function definition(): array
    {
        $name = 'Misa '.fake()->date();

        return ['user_id' => User::factory(), 'name' => $name, 'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999), 'event_type' => 'Misa', 'event_date' => fake()->dateTimeBetween('now', '+2 months'), 'status' => 'draft', 'visibility' => 'private', 'allow_public_download' => false];
    }
}
