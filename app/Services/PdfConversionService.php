<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Imagick;
use Throwable;

class PdfConversionService
{
    public function available(): bool
    {
        return (bool) config('cancionero.pdf_conversion_enabled', false)
            && extension_loaded('imagick')
            && class_exists(Imagick::class);
    }

    public function convert(string $absolutePdfPath, string $pagesDirectory, string $thumbnailsDirectory, ?string $filePrefix = null): array
    {
        if (! $this->available()) {
            return [];
        }

        $pages = [];
        $safePrefix = trim((string) $filePrefix) !== ''
            ? preg_replace('/[^A-Za-z0-9_-]/', '', (string) $filePrefix)
            : null;
        if (! is_string($safePrefix) || $safePrefix === '') {
            $safePrefix = uniqid('pdf_', true);
            $safePrefix = str_replace('.', '', $safePrefix);
        }
        try {
            $imagick = new Imagick;
            $imagick->setResolution((int) config('cancionero.pdf_resolution', 150), (int) config('cancionero.pdf_resolution', 150));
            $imagick->readImage($absolutePdfPath);
            foreach ($imagick as $index => $page) {
                // Ensure pages are flattened against a white background and have no alpha
                $flatten = clone $page;
                $flatten->setImageBackgroundColor('white');
                $flatten = $flatten->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);

                $flatten->setImageFormat('webp');
                $flatten->setImageCompressionQuality((int) config('cancionero.image_quality', 85));
                $pageName = sprintf('%s-page-%04d.webp', $safePrefix, $index + 1);
                $thumbName = sprintf('%s-page-%04d-thumb.webp', $safePrefix, $index + 1);
                $pagePath = $pagesDirectory.DIRECTORY_SEPARATOR.$pageName;
                $thumbPath = $thumbnailsDirectory.DIRECTORY_SEPARATOR.$thumbName;
                $flatten->writeImage($pagePath);
                $thumbnail = clone $flatten;
                $thumbnail->thumbnailImage(360, 480, true, true);
                $thumbnail->writeImage($thumbPath);
                $pages[] = [
                    'page_number' => $index + 1,
                    'stored_name' => $pageName,
                    'page_path' => $pagePath,
                    'thumbnail_path' => $thumbPath,
                    'mime_type' => 'image/webp',
                    'extension' => 'webp',
                    'file_size' => filesize($pagePath) ?: 0,
                ];
                $thumbnail->clear();
                $flatten->clear();
            }
            $imagick->clear();
        } catch (Throwable $exception) {
            Log::warning('No fue posible convertir un PDF a imágenes.', ['exception' => $exception->getMessage()]);

            return [];
        }

        return $pages;
    }
}
