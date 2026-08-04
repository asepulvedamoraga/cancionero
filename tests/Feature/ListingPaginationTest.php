<?php

namespace Tests\Feature;

use App\Models\Repertoire;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingPaginationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_administration_and_recovery_lists_use_supported_page_sizes(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->count(25)->create();
        $songs = Song::factory()->count(25)->for($admin, 'owner')->create();
        $songs->each->delete();
        $repertoires = Repertoire::factory()->count(25)->for($admin, 'owner')->create();
        $repertoires->each->delete();

        $this->actingAs($admin)->get(route('admin.users.index', ['per_page' => 24]))
            ->assertOk()
            ->assertViewHas('users', fn ($users) => $users->perPage() === 24 && $users->count() === 24);

        $this->actingAs($admin)->get(route('songs.archived', ['per_page' => 24]))
            ->assertOk()
            ->assertViewHas('songs', fn ($items) => $items->perPage() === 24 && $items->count() === 24);

        $this->actingAs($admin)->get(route('repertoires.trashed', ['per_page' => 24]))
            ->assertOk()
            ->assertViewHas('repertoires', fn ($items) => $items->perPage() === 24 && $items->count() === 24);
    }
}