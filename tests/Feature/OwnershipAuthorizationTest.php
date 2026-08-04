<?php

namespace Tests\Feature;

use App\Models\Repertoire;
use App\Models\Song;
use App\Models\SongFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OwnershipAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_regular_user_creates_content_owned_by_their_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('songs.store'), [
            'title' => 'Canción propia',
            'is_active' => 1,
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->post(route('repertoires.store'), [
            'name' => 'Repertorio propio',
            'status' => 'draft',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('songs', ['title' => 'Canción propia', 'user_id' => $user->id]);
        $this->assertDatabaseHas('repertoires', [
            'name' => 'Repertorio propio',
            'user_id' => $user->id,
            'visibility' => 'private',
            'allow_public_download' => false,
        ]);
    }

    public function test_user_can_view_active_shared_song_but_cannot_modify_it_or_its_files(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $song = Song::factory()->for($owner, 'owner')->create(['is_active' => true]);
        $file = SongFile::factory()->create(['song_id' => $song->id, 'original_path' => 'songs/shared.jpg']);
        Storage::disk('local')->put($file->original_path, 'image');

        $this->actingAs($other)->get(route('songs.show', $song))->assertOk()->assertSee($owner->name);
        $this->actingAs($other)->get(route('songs.files.show', [$song, $file]))->assertOk();
        $this->actingAs($other)->get(route('songs.edit', $song))->assertForbidden();
        $this->actingAs($other)->put(route('songs.update', $song), ['title' => 'Alterada', 'is_active' => 1])->assertForbidden();
        $this->actingAs($other)->delete(route('songs.files.destroy', [$song, $file]))->assertForbidden();
        $this->actingAs($other)->delete(route('songs.destroy', $song))->assertForbidden();

        $this->assertSame($owner->id, $song->fresh()->user_id);
        $this->assertSame($song->title, $song->fresh()->title);
        $this->assertModelExists($file);
    }

    public function test_inactive_song_is_private_to_owner_and_administrator(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $song = Song::factory()->for($owner, 'owner')->create(['is_active' => false]);

        $this->actingAs($owner)->get(route('songs.show', $song))->assertOk();
        $this->actingAs($other)->get(route('songs.show', $song))->assertForbidden();
        $this->actingAs($admin)->get(route('songs.edit', $song))->assertOk();
    }

    public function test_repertoire_list_accepts_only_supported_page_sizes(): void
    {
        $user = User::factory()->create();
        Repertoire::factory()->count(30)->for($user, 'owner')->create();

        $this->actingAs($user)->get(route('repertoires.index', ['per_page' => 24]))
            ->assertOk()
            ->assertViewHas('repertoires', fn ($repertoires) => $repertoires->perPage() === 24 && $repertoires->count() === 24);

        $this->actingAs($user)->get(route('repertoires.index', ['per_page' => 500]))
            ->assertOk()
            ->assertViewHas('repertoires', fn ($repertoires) => $repertoires->perPage() === 12 && $repertoires->count() === 12);
    }
    public function test_user_sees_public_repertoire_but_cannot_manage_or_export_it(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $public = Repertoire::factory()->for($owner, 'owner')->create(['name' => 'Repertorio visible', 'visibility' => 'public']);
        $private = Repertoire::factory()->for($owner, 'owner')->create(['name' => 'Repertorio oculto', 'visibility' => 'private']);

        $this->actingAs($other)->get(route('repertoires.index'))->assertOk()->assertSee($public->name)->assertDontSee($private->name);
        $this->actingAs($other)->get(route('repertoires.show', $public))->assertOk()->assertSee('Público');
        $this->actingAs($other)->get(route('repertoires.show', $private))->assertForbidden();
        $this->actingAs($other)->get(route('repertoires.edit', $public))->assertForbidden();
        $this->actingAs($other)->post(route('repertoires.duplicate', $public))->assertForbidden();
        $this->actingAs($other)->post(route('repertoires.export', $public))->assertForbidden();
        $this->actingAs($other)->delete(route('repertoires.destroy', $public))->assertForbidden();
    }

    public function test_owner_can_add_another_users_active_song_to_own_repertoire(): void
    {
        $user = User::factory()->create();
        $songOwner = User::factory()->create();
        $repertoire = Repertoire::factory()->for($user, 'owner')->create();
        $song = Song::factory()->for($songOwner, 'owner')->create(['is_active' => true]);

        $this->actingAs($user)->post(route('repertoires.songs.store', $repertoire), ['song_ids' => [$song->id]])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('repertoire_song', ['repertoire_id' => $repertoire->id, 'song_id' => $song->id]);
    }

    public function test_user_cannot_modify_another_users_repertoire_composition(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $repertoire = Repertoire::factory()->for($owner, 'owner')->create(['visibility' => 'public']);
        $song = Song::factory()->create();

        $this->actingAs($other)->post(route('repertoires.songs.store', $repertoire), ['song_ids' => [$song->id]])->assertForbidden();
        $this->assertDatabaseMissing('repertoire_song', ['repertoire_id' => $repertoire->id, 'song_id' => $song->id]);
    }

    public function test_administrator_can_manage_content_from_any_owner(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $song = Song::factory()->for($owner, 'owner')->create();
        $repertoire = Repertoire::factory()->for($owner, 'owner')->create();

        $this->actingAs($admin)->get(route('songs.edit', $song))->assertOk();
        $this->actingAs($admin)->get(route('repertoires.edit', $repertoire))->assertOk();
    }
}
