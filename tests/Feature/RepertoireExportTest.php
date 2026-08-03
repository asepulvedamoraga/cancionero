<?php

namespace Tests\Feature;

use App\Models\Repertoire;
use App\Models\Song;
use App\Models\SongFile;
use App\Models\User;
use App\Services\RepertoireExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RepertoireExportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('local');
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_it_generates_a_pdf_from_images_and_original_pdfs_in_order(): void
    {
        $repertoire = Repertoire::factory()->create(['name' => 'Misa de prueba']);
        [$imageSong, $pdfSong] = Song::factory()->count(2)->create()->all();
        $repertoire->songs()->attach([$imageSong->id => ['sort_order' => 1], $pdfSong->id => ['sort_order' => 2]]);

        $image = UploadedFile::fake()->image('pagina.jpg', 600, 900);
        Storage::disk('local')->put('songs/image.jpg', $image->getContent());
        SongFile::factory()->create(['song_id' => $imageSong->id, 'original_path' => 'songs/image.jpg', 'sort_order' => 1]);

        $sourcePdf = new \FPDF;
        $sourcePdf->AddPage();
        $sourcePdf->SetFont('Helvetica', '', 12);
        $sourcePdf->Cell(0, 10, 'Pagina PDF');
        Storage::disk('local')->put('songs/original.pdf', $sourcePdf->Output('S'));
        SongFile::factory()->create(['song_id' => $pdfSong->id, 'original_path' => 'songs/original.pdf', 'original_name' => 'original.pdf', 'file_type' => 'pdf', 'mime_type' => 'application/pdf', 'extension' => 'pdf', 'sort_order' => 1]);

        $export = app(RepertoireExportService::class)->generate($repertoire);

        Storage::disk('local')->assertExists($export['path']);
        $this->assertStringStartsWith('%PDF-', Storage::disk('local')->get($export['path']));
        $this->assertStringEndsWith('.pdf', $export['name']);
    }

    public function test_admin_can_download_export_and_guest_cannot(): void
    {
        $repertoire = Repertoire::factory()->create(['name' => 'Repertorio descargable']);
        $song = Song::factory()->create();
        $repertoire->songs()->attach($song->id, ['sort_order' => 1]);
        $image = UploadedFile::fake()->image('pagina.jpg', 300, 500);
        Storage::disk('local')->put('songs/page.jpg', $image->getContent());
        SongFile::factory()->create(['song_id' => $song->id, 'original_path' => 'songs/page.jpg']);

        $this->post(route('repertoires.export', $repertoire))->assertRedirect('/login');
        $this->actingAs($this->admin)->post(route('repertoires.export', $repertoire))->assertOk()->assertDownload();
    }

    public function test_export_without_pages_returns_a_clear_validation_error(): void
    {
        $repertoire = Repertoire::factory()->create();

        $this->actingAs($this->admin)->from(route('repertoires.show', $repertoire))->post(route('repertoires.export', $repertoire))
            ->assertRedirect(route('repertoires.show', $repertoire))->assertSessionHasErrors('export');
    }

    public function test_cleanup_command_removes_only_expired_exports(): void
    {
        Storage::disk('local')->put('exports/repertoires/1/old.pdf', 'old');
        Storage::disk('local')->put('exports/repertoires/1/new.pdf', 'new');
        touch(Storage::disk('local')->path('exports/repertoires/1/old.pdf'), now()->subHours(3)->timestamp);

        $this->artisan('repertoire:cleanup-exports', ['--hours' => 2])->assertSuccessful()->expectsOutput('Se eliminaron 1 exportaciones temporales.');

        Storage::disk('local')->assertMissing('exports/repertoires/1/old.pdf');
        Storage::disk('local')->assertExists('exports/repertoires/1/new.pdf');
    }
}
