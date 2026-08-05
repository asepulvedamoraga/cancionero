<?php

namespace Tests\Unit;

use App\Models\Repertoire;
use App\Models\Song;
use App\Models\SongFile;
use App\Services\RepertoirePageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepertoirePageServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_flattens_pages_in_repertoire_and_song_order(): void
    {
        $repertoire = Repertoire::factory()->create();
        [$firstSong, $secondSong] = Song::factory()->count(2)->create()->all();
        $repertoire->songs()->attach([$firstSong->id => ['sort_order' => 2], $secondSong->id => ['sort_order' => 1]]);
        $secondPage = SongFile::factory()->create(['song_id' => $secondSong->id, 'sort_order' => 2]);
        $firstPage = SongFile::factory()->create(['song_id' => $secondSong->id, 'sort_order' => 1]);
        $thirdPage = SongFile::factory()->create(['song_id' => $firstSong->id, 'sort_order' => 1]);

        $pages = app(RepertoirePageService::class)->pages($repertoire);

        $this->assertSame([$firstPage->id, $secondPage->id, $thirdPage->id], collect($pages)->map(fn ($page) => (int) basename($page['image_url']))->all());
        $this->assertSame([1, 2, 3], array_column($pages, 'global_page_position'));
        $this->assertSame([3, 3, 3], array_column($pages, 'total_pages'));
        $this->assertSame([1, 1, 2], array_column($pages, 'song_position'));
        $this->assertSame([1, 2, 1], array_column($pages, 'page_position'));
    }

    public function test_pdf_is_used_without_generated_pages_and_not_duplicated_with_generated_pages(): void
    {
        $repertoire = Repertoire::factory()->create();
        [$pdfOnlySong, $convertedSong] = Song::factory()->count(2)->create()->all();
        $repertoire->songs()->attach([$pdfOnlySong->id => ['sort_order' => 1], $convertedSong->id => ['sort_order' => 2]]);
        SongFile::factory()->create(['song_id' => $pdfOnlySong->id, 'file_type' => 'pdf', 'mime_type' => 'application/pdf', 'extension' => 'pdf']);
        SongFile::factory()->create(['song_id' => $convertedSong->id, 'file_type' => 'pdf', 'mime_type' => 'application/pdf', 'extension' => 'pdf']);
        SongFile::factory()->create(['song_id' => $convertedSong->id, 'file_type' => 'generated_image']);

        $pages = app(RepertoirePageService::class)->pages($repertoire);

        $this->assertSame(['pdf', 'generated_image'], array_column($pages, 'file_type'));
    }

    public function test_it_omits_inactive_songs_and_songs_without_files(): void
    {
        $repertoire = Repertoire::factory()->create();
        $active = Song::factory()->create();
        $inactive = Song::factory()->create(['is_active' => false]);
        $empty = Song::factory()->create();
        $repertoire->songs()->attach([$active->id => ['sort_order' => 1], $inactive->id => ['sort_order' => 2], $empty->id => ['sort_order' => 3]]);
        SongFile::factory()->create(['song_id' => $active->id]);
        SongFile::factory()->create(['song_id' => $inactive->id]);

        $pages = app(RepertoirePageService::class)->pages($repertoire);

        $this->assertCount(1, $pages);
        $this->assertSame($active->id, $pages[0]['song_id']);
        $this->assertSame(1, $pages[0]['song_count']);
    }

    public function test_it_prefers_files_from_the_selected_song_tone(): void
    {
        $repertoire = Repertoire::factory()->create();
        $song = Song::factory()->create(['musical_key' => 'Do']);
        $defaultTone = $song->tones()->where('is_default', true)->firstOrFail();
        $altTone = $song->tones()->create(['name' => 'Re', 'is_default' => false]);

        $defaultFile = SongFile::factory()->create(['song_id' => $song->id, 'song_tone_id' => $defaultTone->id, 'sort_order' => 1]);
        $altFile = SongFile::factory()->create(['song_id' => $song->id, 'song_tone_id' => $altTone->id, 'sort_order' => 2]);

        $repertoire->songs()->attach($song->id, ['sort_order' => 1, 'song_tone_id' => $altTone->id]);

        $pages = app(RepertoirePageService::class)->pages($repertoire);

        $this->assertCount(1, $pages);
        $selectedFileIds = collect($pages)
            ->map(fn ($page) => (int) basename($page['image_url']))
            ->all();

        $this->assertSame([$altFile->id], $selectedFileIds);
    }
}
