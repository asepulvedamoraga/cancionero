<?php

namespace Tests\Feature;

use App\Models\Repertoire;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepertoireRecoveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_owner_can_move_repertoire_to_trash_and_restore_its_composition(): void
    {
        $owner = User::factory()->create();
        $repertoire = Repertoire::factory()->for($owner, 'owner')->create(['visibility' => 'private']);
        $song = Song::factory()->create();
        $repertoire->songs()->attach($song, ['sort_order' => 1, 'notes' => 'Comenzar suave']);

        $this->actingAs($owner)->delete(route('repertoires.destroy', $repertoire))->assertRedirect(route('repertoires.index'));
        $this->assertSoftDeleted($repertoire);
        $this->assertDatabaseHas('repertoire_song', ['repertoire_id' => $repertoire->id, 'song_id' => $song->id, 'notes' => 'Comenzar suave']);

        $this->actingAs($owner)->get(route('repertoires.trashed'))->assertOk()->assertSee($repertoire->name);
        $this->actingAs($owner)->put(route('repertoires.restore', $repertoire->id))->assertRedirect(route('repertoires.show', $repertoire->id));
        $this->assertNotSoftDeleted($repertoire);
        $this->assertSame($song->id, $repertoire->fresh()->songs->firstOrFail()->id);
    }

    public function test_deleting_public_repertoire_revokes_public_access_and_restores_it_as_private(): void
    {
        $owner = User::factory()->create();
        $repertoire = Repertoire::factory()->for($owner, 'owner')->create(['visibility' => 'public', 'allow_public_download' => true]);

        $this->actingAs($owner)->delete(route('repertoires.destroy', $repertoire));
        $this->get(route('public.repertoires.show', ['repertoire' => $repertoire->slug]))->assertNotFound();

        $this->actingAs($owner)->put(route('repertoires.restore', $repertoire->id));
        $repertoire->refresh();
        $this->assertSame('private', $repertoire->visibility);
        $this->assertFalse($repertoire->allow_public_download);
        $this->get(route('public.repertoires.show', ['repertoire' => $repertoire->slug]))->assertNotFound();
    }

    public function test_user_cannot_see_or_restore_another_users_trashed_repertoire(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $repertoire = Repertoire::factory()->for($owner, 'owner')->create();
        $repertoire->delete();

        $this->actingAs($other)->get(route('repertoires.trashed'))->assertOk()->assertDontSee($repertoire->name);
        $this->actingAs($other)->put(route('repertoires.restore', $repertoire->id))->assertForbidden();
        $this->assertSoftDeleted($repertoire);
    }

    public function test_admin_can_see_and_restore_any_trashed_repertoire(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $repertoire = Repertoire::factory()->for($owner, 'owner')->create();
        $repertoire->delete();

        $this->actingAs($admin)->get(route('repertoires.trashed'))->assertOk()->assertSee($repertoire->name)->assertSee($owner->name);
        $this->actingAs($admin)->put(route('repertoires.restore', $repertoire->id))->assertRedirect();
        $this->assertNotSoftDeleted($repertoire);
    }
}
