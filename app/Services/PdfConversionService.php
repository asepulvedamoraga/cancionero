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

    public function convert(string $absolutePdfPath, string $pagesDirectory, string $thumbnailsDirectory): array
    {
        if (! $this->available()) {
            return [];
        }

        $pages = [];
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
                $pageName = sprintf('page-%04d.webp', $index + 1);
                $thumbName = sprintf('page-%04d-thumb.webp', $index + 1);
                $pagePath = $pagesDirectory.DIRECTORY_SEPARATOR.$pageName;
                $thumbPath = $thumbnailsDirectory.DIRECTORY_SEPARATOR.$thumbName;
                $flatten->writeImage($pagePath);
                $thumbnail = clone $flatten;
                $thumbnail->thumbnailImage(360, 480, true, true);
                $thumbnail->writeImage($thumbPath);
                $pages[] = ['page_number' => $index + 1, 'stored_name' => $pageName, 'page_path' => $pagePath, 'thumbnail_path' => $thumbPath, 'mime_type' => 'image/webp', 'extension' => 'webp', 'file_size' => filesize($pagePath) ?: 0];
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
