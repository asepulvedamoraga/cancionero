<?php

namespace Tests\Feature;

use App\Models\Repertoire;
use App\Models\Song;
use App\Models\SongFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicRepertoireTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('local');
    }

    public function test_guest_can_view_public_repertoire_without_internal_notes_or_actions(): void
    {
        $owner = User::factory()->create(['name' => 'Encargado del coro']);
        $repertoire = Repertoire::factory()->for($owner, 'owner')->create(['visibility' => 'public']);
        $active = Song::factory()->create(['title' => 'Canción visible', 'is_active' => true]);
        $inactive = Song::factory()->create(['title' => 'Canción inactiva', 'is_active' => false]);
        $repertoire->songs()->attach([
            $active->id => ['sort_order' => 1, 'notes' => 'Nota interna secreta'],
            $inactive->id => ['sort_order' => 2, 'notes' => null],
        ]);

        $this->get($this->publicRoute('show', $repertoire))
            ->assertOk()
            ->assertSee($repertoire->name)
            ->assertSee('Encargado del coro')
            ->assertSee($active->title)
            ->assertDontSee($inactive->title)
            ->assertDontSee('Nota interna secreta')
            ->assertDontSee('Editar');
    }

    public function test_private_repertoire_and_its_public_endpoints_return_not_found(): void
    {
        $repertoire = Repertoire::factory()->create(['visibility' => 'private', 'allow_public_download' => true]);
        $song = Song::factory()->create();
        $file = SongFile::factory()->create(['song_id' => $song->id]);
        $repertoire->songs()->attach($song->id, ['sort_order' => 1]);

        $this->get($this->publicRoute('show', $repertoire))->assertNotFound();
        $this->get($this->publicRoute('presentation', $repertoire))->assertNotFound();
        $this->get(route('public.repertoires.files.show', ['repertoire' => $repertoire->slug, 'song' => $song, 'file' => $file]))->assertNotFound();
        $this->get($this->publicRoute('download', $repertoire))->assertNotFound();
    }

    public function test_public_presentation_uses_validated_public_file_endpoint(): void
    {
        $repertoire = Repertoire::factory()->create(['visibility' => 'public']);
        $song = Song::factory()->create(['title' => 'Canto público', 'is_active' => true]);
        $file = SongFile::factory()->create(['song_id' => $song->id, 'original_path' => 'songs/public.jpg']);
        Storage::disk('local')->put($file->original_path, 'image-data');
        $repertoire->songs()->attach($song->id, ['sort_order' => 1]);
        $fileUrl = route('public.repertoires.files.show', ['repertoire' => $repertoire->slug, 'song' => $song, 'file' => $file]);

        $this->get($this->publicRoute('presentation', $repertoire))->assertOk()->assertSee($song->title)->assertSee($fileUrl, false);
        $this->get($fileUrl)->assertOk()->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_public_file_endpoint_rejects_unattached_inactive_and_mismatched_files(): void
    {
        $repertoire = Repertoire::factory()->create(['visibility' => 'public']);
        $attached = Song::factory()->create(['is_active' => true]);
        $unattached = Song::factory()->create(['is_active' => true]);
        $inactive = Song::factory()->create(['is_active' => false]);
        $attachedFile = SongFile::factory()->create(['song_id' => $attached->id]);
        $unattachedFile = SongFile::factory()->create(['song_id' => $unattached->id]);
        $inactiveFile = SongFile::factory()->create(['song_id' => $inactive->id]);
        $repertoire->songs()->attach([$attached->id => ['sort_order' => 1], $inactive->id => ['sort_order' => 2]]);

        $this->get(route('public.repertoires.files.show', ['repertoire' => $repertoire->slug, 'song' => $unattached, 'file' => $unattachedFile]))->assertNotFound();
        $this->get(route('public.repertoires.files.show', ['repertoire' => $repertoire->slug, 'song' => $inactive, 'file' => $inactiveFile]))->assertNotFound();
        $this->get(route('public.repertoires.files.show', ['repertoire' => $repertoire->slug, 'song' => $attached, 'file' => $unattachedFile]))->assertNotFound();
    }

    public function test_changing_repertoire_to_private_immediately_revokes_public_file_access(): void
    {
        $repertoire = Repertoire::factory()->create(['visibility' => 'public']);
        $song = Song::factory()->create();
        $file = SongFile::factory()->create(['song_id' => $song->id, 'original_path' => 'songs/revocable.jpg']);
        Storage::disk('local')->put($file->original_path, 'image');
        $repertoire->songs()->attach($song->id, ['sort_order' => 1]);
        $url = route('public.repertoires.files.show', ['repertoire' => $repertoire->slug, 'song' => $song, 'file' => $file]);

        $this->get($url)->assertOk();
        $repertoire->update(['visibility' => 'private']);
        $this->get($url)->assertNotFound();
    }

    public function test_public_pdf_download_requires_explicit_permission(): void
    {
        $repertoire = Repertoire::factory()->create(['visibility' => 'public', 'allow_public_download' => false]);
        $song = Song::factory()->create();
        $repertoire->songs()->attach($song->id, ['sort_order' => 1]);
        $image = UploadedFile::fake()->image('pagina.jpg', 300, 500);
        Storage::disk('local')->put('songs/download.jpg', $image->getContent());
        SongFile::factory()->create(['song_id' => $song->id, 'original_path' => 'songs/download.jpg']);

        $this->get($this->publicRoute('download', $repertoire))->assertNotFound();
        $repertoire->update(['allow_public_download' => true]);
        $this->get($this->publicRoute('download', $repertoire))->assertOk()->assertDownload();
    }

    private function publicRoute(string $action, Repertoire $repertoire): string
    {
        return route('public.repertoires.'.$action, ['repertoire' => $repertoire->slug]);
    }
}
