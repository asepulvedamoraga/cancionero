<?php

namespace Tests\Feature;

use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SharedSongLibraryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_library_shows_active_shared_songs_and_hides_inactive_songs(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $ownActive = Song::factory()->for($user, 'owner')->create(['title' => 'Propia activa', 'is_active' => true]);
        $shared = Song::factory()->for($other, 'owner')->create(['title' => 'Compartida activa', 'is_active' => true]);
        $inactive = Song::factory()->for($user, 'owner')->create(['title' => 'Propia inactiva', 'is_active' => false]);

        $response = $this->actingAs($user)->get(route('songs.index'));

        $response->assertOk()->assertSee($ownActive->title)->assertSee($shared->title)->assertDontSee($inactive->title);
    }

    public function test_my_songs_scope_only_shows_current_users_active_and_inactive_songs(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $ownActive = Song::factory()->for($user, 'owner')->create(['title' => 'Mi canción activa', 'is_active' => true]);
        $ownInactive = Song::factory()->for($user, 'owner')->create(['title' => 'Mi canción inactiva', 'is_active' => false]);
        $shared = Song::factory()->for($other, 'owner')->create(['title' => 'Canción de otra persona']);

        $response = $this->actingAs($user)->get(route('songs.index', ['scope' => 'mine']));

        $response->assertOk()->assertSee($ownActive->title)->assertSee($ownInactive->title)->assertDontSee($shared->title);
    }

    public function test_library_can_filter_by_ownership(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $own = Song::factory()->for($user, 'owner')->create(['title' => 'Subida por mí']);
        $shared = Song::factory()->for($other, 'owner')->create(['title' => 'Subida por otra persona']);

        $this->actingAs($user)->get(route('songs.index', ['ownership' => 'mine']))->assertSee($own->title)->assertDontSee($shared->title);
        $this->actingAs($user)->get(route('songs.index', ['ownership' => 'others']))->assertSee($shared->title)->assertDontSee($own->title);
    }

    public function test_library_accepts_only_supported_page_sizes(): void
    {
        $user = User::factory()->create();
        Song::factory()->count(30)->create(['is_active' => true]);

        $this->actingAs($user)->get(route('songs.index', ['per_page' => 24]))
            ->assertOk()
            ->assertViewHas('songs', fn ($songs) => $songs->perPage() === 24 && $songs->count() === 24);

        $this->actingAs($user)->get(route('songs.index', ['per_page' => 500]))
            ->assertOk()
            ->assertViewHas('songs', fn ($songs) => $songs->perPage() === 12 && $songs->count() === 12);
    }
    public function test_owner_can_restore_archived_song_and_other_user_cannot(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $song = Song::factory()->for($owner, 'owner')->create(['title' => 'Canción archivada']);
        $song->delete();

        $this->actingAs($other)->get(route('songs.archived'))->assertOk()->assertDontSee($song->title);
        $this->actingAs($other)->put(route('songs.restore', $song->id))->assertForbidden();
        $this->assertSoftDeleted($song);

        $this->actingAs($owner)->get(route('songs.archived'))->assertOk()->assertSee($song->title);
        $this->actingAs($owner)->put(route('songs.restore', $song->id))->assertRedirect(route('songs.show', $song->id));
        $this->assertNotSoftDeleted($song);
    }

    public function test_administrator_can_view_and_restore_any_archived_song(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $owner = User::factory()->create();
        $song = Song::factory()->for($owner, 'owner')->create(['title' => 'Archivada por usuario']);
        $song->delete();

        $this->actingAs($admin)->get(route('songs.archived'))->assertSee($song->title);
        $this->actingAs($admin)->put(route('songs.restore', $song->id))->assertRedirect();
        $this->assertNotSoftDeleted($song);
    }

    public function test_suggestions_return_only_active_matches_without_private_account_data(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create(['name' => 'Otra persona', 'email' => 'private@example.com']);
        Song::factory()->for($owner, 'owner')->create(['title' => 'Pescador de hombres', 'author' => 'Autor conocido', 'is_active' => true]);
        Song::factory()->for($owner, 'owner')->create(['title' => 'Pescador archivado', 'is_active' => false]);

        $response = $this->actingAs($user)->getJson(route('songs.suggestions', ['q' => 'Pescador']));

        $response->assertOk()->assertJsonCount(1, 'songs')->assertJsonPath('songs.0.title', 'Pescador de hombres')->assertJsonMissing(['email' => 'private@example.com']);
    }

    public function test_suggestions_require_at_least_three_characters(): void
    {
        $user = User::factory()->create();
        Song::factory()->create(['title' => 'Sol']);

        $this->actingAs($user)->getJson(route('songs.suggestions', ['q' => 'So']))->assertExactJson(['songs' => []]);
    }

    public function test_duplicate_title_is_advisory_and_does_not_block_a_new_version(): void
    {
        $user = User::factory()->create();
        Song::factory()->create(['title' => 'Santo tradicional']);

        $this->actingAs($user)->post(route('songs.store'), ['title' => 'Santo tradicional', 'is_active' => 1])->assertSessionHasNoErrors();

        $this->assertSame(2, Song::where('title', 'Santo tradicional')->count());
    }
}
