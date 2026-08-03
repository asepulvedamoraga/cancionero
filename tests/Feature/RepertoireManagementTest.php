<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\LiturgicalMoment;
use App\Models\LiturgicalSeason;
use App\Models\Repertoire;
use App\Models\Song;
use App\Models\SongFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepertoireManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_guest_cannot_access_repertoires(): void
    {
        $this->get('/repertoires')->assertRedirect('/login');
    }

    public function test_admin_can_create_and_update_a_repertoire(): void
    {
        $response = $this->actingAs($this->admin)->post(route('repertoires.store'), ['name' => 'Misa domingo', 'event_date' => '2026-08-09', 'event_time' => '12:30', 'status' => 'draft']);
        $repertoire = Repertoire::firstOrFail();
        $response->assertRedirect(route('repertoires.edit', $repertoire));
        $this->assertSame('misa-domingo', $repertoire->slug);
        $this->actingAs($this->admin)->put(route('repertoires.update', $repertoire), ['name' => 'Misa principal', 'status' => 'ready'])->assertRedirect(route('repertoires.edit', $repertoire));
        $this->assertSame('ready', $repertoire->fresh()->status);
    }

    public function test_admin_can_add_song_and_duplicate_is_rejected(): void
    {
        $repertoire = Repertoire::factory()->create();
        $song = Song::factory()->create(['is_active' => true]);
        $this->actingAs($this->admin)->post(route('repertoires.songs.store', $repertoire), ['song_ids' => [$song->id]])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('repertoire_song', ['repertoire_id' => $repertoire->id, 'song_id' => $song->id, 'sort_order' => 1]);
        $this->actingAs($this->admin)->post(route('repertoires.songs.store', $repertoire), ['song_ids' => [$song->id]])->assertSessionHasErrors('song_ids');
        $this->assertDatabaseCount('repertoire_song', 1);
    }

    public function test_inactive_song_cannot_be_added(): void
    {
        $repertoire = Repertoire::factory()->create();
        $song = Song::factory()->create(['is_active' => false]);
        $this->actingAs($this->admin)->post(route('repertoires.songs.store', $repertoire), ['song_ids' => [$song->id]])->assertSessionHasErrors('song_ids.0');
    }

    public function test_search_finds_all_terms_in_title_author_or_performer_and_excludes_selected(): void
    {
        $repertoire = Repertoire::factory()->create();
        $matching = Song::factory()->create(['title' => 'Pescador de hombres', 'author' => 'Cesáreo Gabarain', 'performer' => 'Coro principal']);
        $selected = Song::factory()->create(['title' => 'Pescador ya agregado', 'author' => 'Cesáreo Gabarain']);
        $unrelated = Song::factory()->create(['title' => 'Santo tradicional', 'author' => 'Otro autor']);
        $repertoire->songs()->attach($selected->id, ['sort_order' => 1]);

        $response = $this->actingAs($this->admin)->get(route('repertoires.edit', ['repertoire' => $repertoire, 'song_q' => 'Pescador hombres']));

        $response->assertOk()->assertSee($matching->title)->assertDontSee('id="song-'.$selected->id.'"', false)->assertDontSee($unrelated->title);
        $this->actingAs($this->admin)->get(route('repertoires.edit', ['repertoire' => $repertoire, 'song_q' => 'Coro principal']))->assertSee($matching->title);
    }

    public function test_song_selector_applies_catalog_filters(): void
    {
        $repertoire = Repertoire::factory()->create();
        $category = Category::factory()->create();
        $moment = LiturgicalMoment::factory()->create();
        $season = LiturgicalSeason::factory()->create();
        $matching = Song::factory()->create(['title' => 'Canción filtrada', 'category_id' => $category->id, 'liturgical_moment_id' => $moment->id]);
        $matching->liturgicalSeasons()->attach($season);
        $other = Song::factory()->create(['title' => 'Canción fuera del filtro']);

        $response = $this->actingAs($this->admin)->get(route('repertoires.edit', [
            'repertoire' => $repertoire,
            'category_id' => $category->id,
            'liturgical_moment_id' => $moment->id,
            'liturgical_season_id' => $season->id,
        ]));

        $response->assertOk()->assertSee($matching->title)->assertDontSee($other->title);
    }

    public function test_admin_can_add_multiple_songs_in_the_submitted_order(): void
    {
        $repertoire = Repertoire::factory()->create();
        $songs = Song::factory()->count(3)->create();

        $this->actingAs($this->admin)->post(route('repertoires.songs.store', $repertoire), [
            'song_ids' => [$songs[2]->id, $songs[0]->id, $songs[1]->id],
        ])->assertRedirect(route('repertoires.edit', $repertoire));

        $this->assertSame([$songs[2]->id, $songs[0]->id, $songs[1]->id], $repertoire->fresh()->songs->pluck('id')->all());
        $this->assertSame([1, 2, 3], $repertoire->fresh()->songs->pluck('pivot.sort_order')->all());
    }

    public function test_admin_can_reorder_update_notes_and_remove_songs(): void
    {
        $repertoire = Repertoire::factory()->create();
        [$first, $second] = Song::factory()->count(2)->create()->all();
        $repertoire->songs()->attach([$first->id => ['sort_order' => 1], $second->id => ['sort_order' => 2]]);
        $this->actingAs($this->admin)->putJson(route('repertoires.songs.reorder', $repertoire), ['songs' => [$second->id, $first->id]])->assertOk();
        $this->assertSame(1, $repertoire->songs()->whereKey($second->id)->firstOrFail()->pivot->sort_order);
        $this->actingAs($this->admin)->put(route('repertoires.songs.update', [$repertoire, $second]), ['notes' => 'Comenzar suave'])->assertRedirect();
        $this->assertSame('Comenzar suave', $repertoire->songs()->whereKey($second->id)->firstOrFail()->pivot->notes);
        $this->actingAs($this->admin)->delete(route('repertoires.songs.destroy', [$repertoire, $second]))->assertRedirect();
        $this->assertDatabaseMissing('repertoire_song', ['repertoire_id' => $repertoire->id, 'song_id' => $second->id]);
        $this->assertSame(1, $repertoire->songs()->firstOrFail()->pivot->sort_order);
    }

    public function test_reorder_must_contain_exactly_attached_songs(): void
    {
        $repertoire = Repertoire::factory()->create();
        $songs = Song::factory()->count(2)->create();
        $repertoire->songs()->attach([$songs[0]->id => ['sort_order' => 1], $songs[1]->id => ['sort_order' => 2]]);
        $this->actingAs($this->admin)->putJson(route('repertoires.songs.reorder', $repertoire), ['songs' => [$songs[0]->id]])->assertUnprocessable()->assertJsonValidationErrors('songs');
    }

    public function test_duplicate_copies_songs_order_and_notes_as_draft(): void
    {
        $repertoire = Repertoire::factory()->create(['name' => 'Misa original', 'status' => 'ready']);
        $songs = Song::factory()->count(2)->create();
        $repertoire->songs()->attach([$songs[0]->id => ['sort_order' => 2, 'notes' => 'Nota A'], $songs[1]->id => ['sort_order' => 1, 'notes' => 'Nota B']]);
        $this->actingAs($this->admin)->post(route('repertoires.duplicate', $repertoire))->assertRedirect();
        $copy = Repertoire::whereKeyNot($repertoire->id)->firstOrFail();
        $this->assertSame('draft', $copy->status);
        $this->assertSame(['Nota B', 'Nota A'], $copy->songs->pluck('pivot.notes')->all());
        $this->assertSame([1, 2], $copy->songs->pluck('pivot.sort_order')->all());
    }

    public function test_admin_can_open_repertoire_presentation(): void
    {
        $repertoire = Repertoire::factory()->create();
        $song = Song::factory()->create(['title' => 'Canto de entrada']);
        $file = SongFile::factory()->create(['song_id' => $song->id, 'sort_order' => 1]);
        $repertoire->songs()->attach($song->id, ['sort_order' => 1]);

        $this->actingAs($this->admin)->get(route('repertoires.presentation', $repertoire))
            ->assertOk()
            ->assertSee('Canto de entrada')
            ->assertSee(route('songs.files.show', [$song, $file]), false);
    }

    public function test_guest_cannot_open_repertoire_presentation(): void
    {
        $repertoire = Repertoire::factory()->create();

        $this->get(route('repertoires.presentation', $repertoire))->assertRedirect('/login');
    }

    public function test_edit_shows_page_count_and_warns_when_song_has_no_pages(): void
    {
        $repertoire = Repertoire::factory()->create();
        $withPage = Song::factory()->create();
        $withoutPage = Song::factory()->create();
        SongFile::factory()->create(['song_id' => $withPage->id, 'file_type' => 'image']);
        $repertoire->songs()->attach([$withPage->id => ['sort_order' => 1], $withoutPage->id => ['sort_order' => 2]]);
        $this->actingAs($this->admin)->get(route('repertoires.edit', $repertoire))->assertOk()->assertSee('1 página')->assertSee('Sin páginas disponibles');
    }
}
