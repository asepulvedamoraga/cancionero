<?php

namespace App\Services;

use App\Models\Song;
use App\Models\SongFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class SongFileService
{
    public function __construct(private readonly PdfConversionService $pdfConversionService) {}

    public function storeUploads(Song $song, array $uploads, ?int $toneId = null): array
    {
        $created = [];
        $paths = [];
        $warnings = [];
        $resolvedToneId = $song->resolveToneId($toneId);
        try {
            DB::transaction(function () use ($song, $uploads, $resolvedToneId, &$created, &$paths, &$warnings): void {
                $sortOrder = ((int) $song->files()->max('sort_order')) + 1;
                foreach ($uploads as $upload) {
                    if (! $upload instanceof UploadedFile) {
                        continue;
                    }
                    $mime = (string) $upload->getMimeType();
                    if ($mime === 'application/pdf') {
                        [$records, $storedPaths, $warning] = $this->storePdf($song, $upload, $sortOrder, $resolvedToneId);
                        $created = [...$created, ...$records];
                        $paths = [...$paths, ...$storedPaths];
                        $sortOrder += count($records);
                        if ($warning) {
                            $warnings[] = $warning;
                        }
                    } else {
                        [$record, $storedPaths] = $this->storeImage($song, $upload, $sortOrder++, $resolvedToneId);
                        $created[] = $record;
                        $paths = [...$paths, ...$storedPaths];
                    }
                }
            });
        } catch (Throwable $exception) {
            foreach ($paths as $path) {
                Storage::disk('local')->delete($path);
            }
            throw $exception;
        }

        return ['files' => $created, 'warnings' => $warnings];
    }

    public function replace(SongFile $file, UploadedFile $upload): array
    {
        $song = $file->song;
        $position = $file->sort_order;
        $result = $this->storeUploads($song, [$upload], $file->song_tone_id ? (int) $file->song_tone_id : null);
        $replacement = collect($result['files'])->first();
        if ($replacement) {
            $replacement->update(['sort_order' => $position]);
        }
        $this->delete($file);
        $ids = $song->files()->orderBy('sort_order')->orderBy('id')->pluck('id')->map(fn ($id) => (int) $id)->all();
        if ($ids) {
            $this->reorder($song, $ids);
        }

        return $result;
    }

    public function delete(SongFile $file): void
    {
        $paths = array_filter([$file->original_path, $file->preview_path]);
        DB::transaction(fn () => $file->delete());
        foreach ($paths as $path) {
            try {
                Storage::disk('local')->delete($path);
            } catch (Throwable $exception) {
                Log::warning('No fue posible eliminar un archivo físico.', ['path' => $path, 'exception' => $exception->getMessage()]);
            }
        }
    }

    public function reorder(Song $song, array $fileIds): void
    {
        $owned = $song->files()->whereIn('id', $fileIds)->pluck('id')->map(fn ($id) => (int) $id)->all();
        if (count($owned) !== count($fileIds) || array_diff($fileIds, $owned)) {
            throw new RuntimeException('El orden contiene archivos que no pertenecen a la canción.');
        }
        DB::transaction(function () use ($fileIds): void {
            foreach ($fileIds as $order => $id) {
                SongFile::whereKey($id)->update(['sort_order' => $order + 1]);
            }
        });
    }

    public function purgeSong(Song $song): void
    {
        DB::transaction(fn () => $song->forceDelete());

        try {
            Storage::disk('local')->deleteDirectory("songs/{$song->id}");
        } catch (Throwable $exception) {
            Log::warning('No fue posible eliminar el directorio físico de una canción.', [
                'song_id' => $song->id,
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    private function storeImage(Song $song, UploadedFile $upload, int $sortOrder, int $toneId): array
    {
        $extension = strtolower($upload->guessExtension() ?: $upload->extension());
        $storedName = Str::uuid().'.'.$extension;
        $originalPath = $upload->storeAs("songs/{$song->id}/originals", $storedName, 'local');
        if (! $originalPath) {
            throw new RuntimeException('No se pudo guardar la imagen.');
        }
        $previewPath = $this->createThumbnail($originalPath, $song->id, pathinfo($storedName, PATHINFO_FILENAME));
        $file = $song->files()->create(['song_tone_id' => $toneId, 'original_name' => $upload->getClientOriginalName(), 'stored_name' => $storedName, 'original_path' => $originalPath, 'preview_path' => $previewPath, 'mime_type' => (string) $upload->getMimeType(), 'extension' => $extension, 'file_type' => 'image', 'file_size' => $upload->getSize(), 'sort_order' => $sortOrder, 'is_generated' => false]);

        return [$file, array_filter([$originalPath, $previewPath])];
    }

    private function storePdf(Song $song, UploadedFile $upload, int $sortOrder, int $toneId): array
    {
        $storedName = Str::uuid().'.pdf';
        $originalPath = $upload->storeAs("songs/{$song->id}/originals", $storedName, 'local');
        if (! $originalPath) {
            throw new RuntimeException('No se pudo guardar el PDF.');
        }
        $pdf = $song->files()->create(['song_tone_id' => $toneId, 'original_name' => $upload->getClientOriginalName(), 'stored_name' => $storedName, 'original_path' => $originalPath, 'mime_type' => 'application/pdf', 'extension' => 'pdf', 'file_type' => 'pdf', 'file_size' => $upload->getSize(), 'sort_order' => $sortOrder, 'is_generated' => false]);
        $records = [$pdf];
        $paths = [$originalPath];
        if (! $this->pdfConversionService->available()) {
            return [$records, $paths, 'Imagick no está disponible. El PDF se conservó y puede abrirse o descargarse, pero sus páginas no fueron convertidas a imágenes.'];
        }
        $disk = Storage::disk('local');
        $pagesDir = $disk->path("songs/{$song->id}/pages");
        $thumbsDir = $disk->path("songs/{$song->id}/thumbnails");
        if (! is_dir($pagesDir)) {
            mkdir($pagesDir, 0775, true);
        } if (! is_dir($thumbsDir)) {
            mkdir($thumbsDir, 0775, true);
        }
        $filePrefix = pathinfo($storedName, PATHINFO_FILENAME).'-tone-'.$toneId;
        $converted = $this->pdfConversionService->convert($disk->path($originalPath), $pagesDir, $thumbsDir, $filePrefix);
        foreach ($converted as $index => $page) {
            $pagePath = "songs/{$song->id}/pages/".basename((string) $page['page_path']);
            $thumbPath = "songs/{$song->id}/thumbnails/".basename((string) $page['thumbnail_path']);
            $records[] = $song->files()->create(['song_tone_id' => $toneId, 'original_name' => $upload->getClientOriginalName().' · página '.$page['page_number'], 'stored_name' => $page['stored_name'], 'original_path' => $pagePath, 'preview_path' => $thumbPath, 'mime_type' => $page['mime_type'], 'extension' => $page['extension'], 'file_type' => 'generated_image', 'file_size' => $page['file_size'], 'page_number' => $page['page_number'], 'sort_order' => $sortOrder + $index + 1, 'is_generated' => true]);
            $paths[] = $pagePath;
            $paths[] = $thumbPath;
        }

        return [$records, $paths, $converted ? null : 'El PDF se guardó, pero no fue posible convertir sus páginas.'];
    }

    private function createThumbnail(string $originalPath, int $songId, string $baseName): ?string
    {
        if (! config('cancionero.generate_thumbnails', true) || ! extension_loaded('gd')) {
            return null;
        }
        $absolute = Storage::disk('local')->path($originalPath);
        $info = @getimagesize($absolute);
        if (! $info) {
            return null;
        }
        $source = match ($info['mime']) {
            'image/jpeg' => @imagecreatefromjpeg($absolute), 'image/png' => @imagecreatefrompng($absolute), 'image/webp' => @imagecreatefromwebp($absolute), default => false
        };
        if (! $source) {
            return null;
        } $ratio = min(360 / imagesx($source), 480 / imagesy($source), 1);
        $width = max(1, (int) round(imagesx($source) * $ratio));
        $height = max(1, (int) round(imagesy($source) * $ratio));
        $thumb = imagecreatetruecolor($width, $height);
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source));
        $path = "songs/{$songId}/thumbnails/{$baseName}.webp";
        Storage::disk('local')->makeDirectory("songs/{$songId}/thumbnails");
        imagewebp($thumb, Storage::disk('local')->path($path), (int) config('cancionero.image_quality', 85));
        imagedestroy($source);
        imagedestroy($thumb);

        return $path;
    }
}
