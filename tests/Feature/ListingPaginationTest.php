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

    public function test_public_landing_lists_public_songs_with_search_and_pagination(): void
    {
        $owner = User::factory()->create();

        $publicRepertoire = Repertoire::factory()->for($owner, 'owner')->create(['visibility' => 'public']);
        $privateRepertoire = Repertoire::factory()->for($owner, 'owner')->create(['visibility' => 'private']);

        $publicSong = Song::factory()->for($owner, 'owner')->create(['title' => 'Aleluya del domingo', 'is_active' => true]);
        $secondPublicSong = Song::factory()->for($owner, 'owner')->create(['title' => 'Canto de esperanza', 'is_active' => true]);
        $privateSong = Song::factory()->for($owner, 'owner')->create(['title' => 'Secreto del retiro', 'is_active' => true]);

        $publicSong->repertoires()->sync([$publicRepertoire->id => ['song_tone_id' => 1]]);
        $secondPublicSong->repertoires()->sync([$publicRepertoire->id => ['song_tone_id' => 1]]);
        $privateSong->repertoires()->sync([$privateRepertoire->id => ['song_tone_id' => 1]]);

        $this->get(route('public.home', ['search' => 'aleluya']))
            ->assertOk()
            ->assertViewHas('publicSongs', fn ($songs) => $songs->total() === 1 && $songs->first()->song->title === 'Aleluya del domingo');
    }
}