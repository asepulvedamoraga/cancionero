<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\LiturgicalMoment;
use App\Models\LiturgicalSeason;
use App\Models\Song;
use App\Models\SongFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SongManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_guest_cannot_access_songs(): void
    {
        $this->get('/songs')->assertRedirect('/login');
    }

    public function test_admin_can_create_and_update_a_song_with_relations(): void
    {
        $category = Category::factory()->create();
        $moment = LiturgicalMoment::factory()->create();
        $season = LiturgicalSeason::factory()->create();
        $response = $this->actingAs($this->admin)->post(route('songs.store'), ['title' => 'Pescador de hombres', 'author' => 'Cesáreo Gabaráin', 'category_id' => $category->id, 'liturgical_moment_id' => $moment->id, 'liturgical_seasons' => [$season->id], 'is_active' => 1]);
        $song = Song::firstOrFail();
        $response->assertRedirect(route('songs.show', $song));
        $this->assertSame('pescador-de-hombres', $song->slug);
        $this->assertTrue($song->liturgicalSeasons->contains($season));
        $this->actingAs($this->admin)->put(route('songs.update', $song), ['title' => 'Pescador de hombres actualizado', 'slug' => $song->slug, 'is_active' => 0])->assertRedirect(route('songs.edit', $song));
        $this->assertFalse($song->fresh()->is_active);
    }

    public function test_admin_can_store_optional_youtube_video_and_see_it_in_song_view(): void
    {
        $response = $this->actingAs($this->admin)->post(route('songs.store'), [
            'title' => 'Con video',
            'is_active' => 1,
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        $song = Song::firstOrFail();
        $response->assertRedirect(route('songs.show', $song));
        $this->assertSame('https://www.youtube.com/watch?v=dQw4w9WgXcQ', $song->video_url);
        $this->actingAs($this->admin)->get(route('songs.show', $song))->assertOk()->assertSee('Video de apoyo')->assertSee('youtube-nocookie.com/embed/dQw4w9WgXcQ', false);
        $this->actingAs($this->admin)->get(route('songs.read', $song))->assertOk()->assertSee('Video de apoyo')->assertSee('youtube-nocookie.com/embed/dQw4w9WgXcQ', false);
    }

    public function test_non_youtube_video_url_is_rejected(): void
    {
        $this->actingAs($this->admin)->post(route('songs.store'), [
            'title' => 'Video inválido',
            'is_active' => 1,
            'video_url' => 'https://example.com/watch?v=dQw4w9WgXcQ',
        ])->assertSessionHasErrors('video_url');
    }

    public function test_admin_can_upload_valid_image_and_private_file_is_recorded(): void
    {
        Storage::fake('local');
        $image = UploadedFile::fake()->image('pagina.jpg', 800, 1200)->size(500);
        $this->actingAs($this->admin)->post(route('songs.store'), ['title' => 'Gloria', 'is_active' => 1, 'files' => [$image]])->assertSessionHasNoErrors();
        $file = SongFile::firstOrFail();
        $this->assertSame('image', $file->file_type);
        Storage::disk('local')->assertExists($file->original_path);
    }

    public function test_invalid_executable_file_is_rejected(): void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->createWithContent('malware.php', '<?php echo 1;');
        $this->actingAs($this->admin)->post(route('songs.store'), ['title' => 'Archivo inválido', 'is_active' => 1, 'files' => [$file]])->assertSessionHasErrors('files.0');
        $this->assertDatabaseCount('songs', 0);
    }

    public function test_pdf_is_kept_when_conversion_is_disabled(): void
    {
        $pdf = UploadedFile::fake()->createWithContent('canto.pdf', '%PDF-1.4 test');
        $response = $this->actingAs($this->admin)->post(route('songs.store'), ['title' => 'Canto PDF', 'is_active' => 1, 'files' => [$pdf]]);
        $response->assertSessionHasNoErrors()->assertSessionHas('warnings');
        $file = SongFile::firstOrFail();
        $this->assertSame('pdf', $file->file_type);
        Storage::disk('local')->assertExists($file->original_path);
    }

    public function test_admin_can_reorder_and_delete_song_files(): void
    {
        Storage::fake('local');
        $song = Song::factory()->create();
        $first = SongFile::factory()->create(['song_id' => $song->id, 'sort_order' => 1, 'original_path' => 'songs/a.jpg']);
        $second = SongFile::factory()->create(['song_id' => $song->id, 'sort_order' => 2, 'original_path' => 'songs/b.jpg']);
        Storage::disk('local')->put('songs/a.jpg', 'a');
        Storage::disk('local')->put('songs/b.jpg', 'b');
        $this->actingAs($this->admin)->putJson(route('songs.files.reorder', $song), ['files' => [$second->id, $first->id]])->assertOk();
        $this->assertSame(1, $second->fresh()->sort_order);
        $this->actingAs($this->admin)->delete(route('songs.files.destroy', [$song, $first]))->assertRedirect();
        $this->assertModelMissing($first);
        Storage::disk('local')->assertMissing('songs/a.jpg');
    }

    public function test_admin_can_replace_a_file_and_open_read_mode(): void
    {
        Storage::fake('local');
        $song = Song::factory()->create();
        $old = SongFile::factory()->create(['song_id' => $song->id, 'original_path' => 'songs/old.jpg', 'sort_order' => 1]);
        Storage::disk('local')->put('songs/old.jpg', 'old');
        $replacement = UploadedFile::fake()->image('new.jpg', 600, 900);
        $this->actingAs($this->admin)->put(route('songs.files.replace', [$song, $old]), ['file' => $replacement])->assertRedirect();
        $this->assertModelMissing($old);
        $new = SongFile::firstOrFail();
        $this->assertSame(1, $new->sort_order);
        Storage::disk('local')->assertMissing('songs/old.jpg');
        $this->actingAs($this->admin)->get(route('songs.read', $song))->assertOk()->assertSee($song->title);
    }

    public function test_file_from_another_song_cannot_be_accessed(): void
    {
        $song = Song::factory()->create();
        $other = Song::factory()->create();
        $file = SongFile::factory()->create(['song_id' => $other->id]);
        $this->actingAs($this->admin)->get(route('songs.files.show', [$song, $file]))->assertNotFound();
    }
}
