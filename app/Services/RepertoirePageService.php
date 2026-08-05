<?php

namespace App\Services;

use App\Models\Repertoire;
use Illuminate\Support\Collection;

class RepertoirePageService
{
    public function pages(Repertoire $repertoire, bool $public = false): array
    {
        $repertoire->loadMissing('songs.files', 'songs.tones');

        $groups = $repertoire->songs
            ->where('is_active', true)
            ->map(function ($song) {
                $requestedToneId = (int) ($song->pivot->song_tone_id ?? 0);
                $defaultToneId = (int) optional($song->tones->firstWhere('is_default', true))->id;
                $toneId = $requestedToneId > 0 ? $requestedToneId : $defaultToneId;

                $files = $song->files;
                if ($toneId > 0) {
                    $toneFiles = $files->where('song_tone_id', $toneId);
                    if ($toneFiles->isNotEmpty()) {
                        $files = $toneFiles;
                    }
                }

                return [
                    'song' => $song,
                    'files' => $this->displayableFiles($files->values()),
                ];
            })
            ->filter(fn ($group) => $group['files']->isNotEmpty())
            ->values();

        $pages = [];
        $globalPosition = 1;
        foreach ($groups as $songIndex => $group) {
            $song = $group['song'];
            foreach ($group['files'] as $pageIndex => $file) {
                $pages[] = [
                    'song_id' => $song->id,
                    'song_title' => $song->title,
                    'song_position' => $songIndex + 1,
                    'song_count' => $groups->count(),
                    'page_position' => $pageIndex + 1,
                    'song_page_count' => $group['files']->count(),
                    'global_page_position' => $globalPosition++,
                    'total_pages' => 0,
                    'image_url' => $public
                        ? route('public.repertoires.files.show', ['repertoire' => $repertoire->slug, 'song' => $song, 'file' => $file])
                        : route('songs.files.show', [$song, $file]),
                    'file_type' => $file->file_type,
                    'mime_type' => $file->mime_type,
                    'original_name' => $file->original_name,
                ];
            }
        }

        $total = count($pages);
        foreach ($pages as &$page) {
            $page['total_pages'] = $total;
        }

        return $pages;
    }

    private function displayableFiles(Collection $files): Collection
    {
        $hasGeneratedPages = $files->contains('file_type', 'generated_image');

        return $files->filter(fn ($file) => in_array($file->file_type, ['image', 'generated_image'], true)
            || ($file->file_type === 'pdf' && ! $hasGeneratedPages))->values();
    }
}
