<?php

namespace Database\Factories;

use App\Models\Song;
use Illuminate\Database\Eloquent\Factories\Factory;

class SongFileFactory extends Factory
{
    public function definition(): array
    {
        return ['song_id' => Song::factory(), 'song_tone_id' => null, 'original_name' => 'page.jpg', 'stored_name' => fake()->uuid().'.jpg', 'original_path' => 'songs/example/originals/page.jpg', 'mime_type' => 'image/jpeg', 'extension' => 'jpg', 'file_type' => 'image', 'file_size' => 1024, 'sort_order' => 0, 'is_generated' => false];
    }
}
