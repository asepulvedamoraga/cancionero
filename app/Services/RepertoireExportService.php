<?php

namespace App\Services;

use App\Models\Repertoire;
use App\Models\SongFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use setasign\Fpdi\Fpdi;
use Throwable;

class RepertoireExportService
{
    public function generate(Repertoire $repertoire): array
    {
        $repertoire->loadMissing('songs.files');
        $groups = $repertoire->songs->where('is_active', true)->map(function ($song): array {
            $hasGenerated = $song->files->contains('file_type', 'generated_image');
            $files = $song->files->filter(fn ($file) => in_array($file->file_type, ['image', 'generated_image'], true)
                || ($file->file_type === 'pdf' && ! $hasGenerated))->values();

            return ['song' => $song, 'files' => $files];
        })->filter(fn ($group) => $group['files']->isNotEmpty())->values();

        if ($groups->isEmpty()) {
            throw new RuntimeException('El repertorio no tiene páginas disponibles para exportar.');
        }

        $directory = "exports/repertoires/{$repertoire->id}";
        Storage::disk('local')->makeDirectory($directory);
        $baseName = Str::slug($repertoire->name) ?: 'repertorio';
        $fileName = $baseName.'-'.now()->format('Ymd-His').'-'.Str::lower(Str::random(6)).'.pdf';
        $relativePath = "{$directory}/{$fileName}";
        $temporaryPath = $relativePath.'.part';
        $temporaryImages = [];

        try {
            $pdf = new Fpdi;
            $pdf->SetTitle($this->pdfText($repertoire->name));
            $pdf->SetAuthor($this->pdfText(config('cancionero.name')));
            $pdf->SetAutoPageBreak(false);

            if (config('cancionero.export.include_cover', true)) {
                $this->addCover($pdf, $repertoire);
            }
            if (config('cancionero.export.include_index', false)) {
                $this->addIndex($pdf, $groups->pluck('song')->all());
            }

            foreach ($groups as $group) {
                foreach ($group['files'] as $file) {
                    $file->file_type === 'pdf'
                        ? $this->appendPdf($pdf, $file)
                        : $this->appendImage($pdf, $file, $temporaryImages);
                }
            }

            $pdf->Output('F', Storage::disk('local')->path($temporaryPath));
            if (! Storage::disk('local')->move($temporaryPath, $relativePath)) {
                throw new RuntimeException('No fue posible finalizar el archivo exportado.');
            }
        } catch (Throwable $exception) {
            Storage::disk('local')->delete([$temporaryPath, $relativePath, ...$temporaryImages]);
            Log::error('No fue posible exportar el repertorio.', ['repertoire_id' => $repertoire->id, 'exception' => $exception->getMessage()]);
            throw new RuntimeException('No fue posible generar el PDF. Revisa que los documentos no estén cifrados o dañados.', previous: $exception);
        } finally {
            Storage::disk('local')->delete($temporaryImages);
        }

        return ['path' => $relativePath, 'name' => $fileName];
    }

    private function addCover(Fpdi $pdf, Repertoire $repertoire): void
    {
        $pdf->AddPage('P', 'A4');
        $pdf->SetMargins(20, 20, 20);
        $pdf->SetY(62);
        $pdf->SetFont('Helvetica', 'B', 25);
        $pdf->MultiCell(0, 12, $this->pdfText($repertoire->name), 0, 'C');
        $pdf->Ln(8);
        $pdf->SetFont('Helvetica', '', 13);
        foreach (array_filter([
            $repertoire->event_type,
            $repertoire->event_date?->format('d-m-Y').($repertoire->event_time ? ' · '.$repertoire->event_time->format('H:i') : ''),
            $repertoire->location,
        ]) as $line) {
            $pdf->Cell(0, 8, $this->pdfText($line), 0, 1, 'C');
        }
    }

    private function addIndex(Fpdi $pdf, array $songs): void
    {
        $pdf->AddPage('P', 'A4');
        $pdf->SetMargins(18, 18, 18);
        $pdf->SetFont('Helvetica', 'B', 20);
        $pdf->Cell(0, 12, $this->pdfText('Índice'), 0, 1);
        $pdf->Ln(3);
        foreach ($songs as $index => $song) {
            if ($pdf->GetY() > 275) {
                $pdf->AddPage('P', 'A4');
            }
            $pdf->SetFont('Helvetica', 'B', 11);
            $pdf->Cell(10, 8, (string) ($index + 1).'.', 0, 0);
            $pdf->SetFont('Helvetica', '', 11);
            $pdf->MultiCell(0, 8, $this->pdfText($song->title));
        }
    }

    private function appendPdf(Fpdi $pdf, SongFile $file): void
    {
        $absolutePath = $this->existingPath($file);
        $pageCount = $pdf->setSourceFile($absolutePath);
        for ($page = 1; $page <= $pageCount; $page++) {
            $template = $pdf->importPage($page);
            $size = $pdf->getTemplateSize($template);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($template, 0, 0, $size['width'], $size['height']);
        }
    }

    private function appendImage(Fpdi $pdf, SongFile $file, array &$temporaryImages): void
    {
        $absolutePath = $this->existingPath($file);
        $info = @getimagesize($absolutePath);
        if (! $info || empty($info[0]) || empty($info[1])) {
            throw new RuntimeException("La imagen {$file->original_name} no es válida.");
        }
        if ($info['mime'] === 'image/webp') {
            $absolutePath = $this->convertWebp($absolutePath, $file, $temporaryImages);
        }

        $orientation = $info[0] > $info[1] ? 'L' : 'P';
        $pdf->AddPage($orientation, 'A4');
        [$pageWidth, $pageHeight] = $orientation === 'L' ? [297.0, 210.0] : [210.0, 297.0];
        $margin = 5;
        $ratio = min(($pageWidth - ($margin * 2)) / $info[0], ($pageHeight - ($margin * 2)) / $info[1]);
        $width = $info[0] * $ratio;
        $height = $info[1] * $ratio;
        $pdf->Image($absolutePath, ($pageWidth - $width) / 2, ($pageHeight - $height) / 2, $width, $height);
    }

    private function convertWebp(string $absolutePath, SongFile $file, array &$temporaryImages): string
    {
        if (! extension_loaded('gd') || ! function_exists('imagecreatefromwebp')) {
            throw new RuntimeException("El archivo {$file->original_name} requiere GD con soporte WebP.");
        }
        $image = @imagecreatefromwebp($absolutePath);
        if (! $image) {
            throw new RuntimeException("No fue posible leer {$file->original_name}.");
        }
        $relativePath = "exports/tmp/{$file->id}-".Str::random(10).'.jpg';
        Storage::disk('local')->makeDirectory('exports/tmp');
        $convertedPath = Storage::disk('local')->path($relativePath);
        if (! imagejpeg($image, $convertedPath, (int) config('cancionero.image_quality', 85))) {
            imagedestroy($image);
            throw new RuntimeException("No fue posible convertir {$file->original_name}.");
        }
        imagedestroy($image);
        $temporaryImages[] = $relativePath;

        return $convertedPath;
    }

    private function existingPath(SongFile $file): string
    {
        if (! Storage::disk('local')->exists($file->original_path)) {
            throw new RuntimeException("No se encontró {$file->original_name}.");
        }

        return Storage::disk('local')->path($file->original_path);
    }

    private function pdfText(?string $value): string
    {
        return iconv('UTF-8', 'windows-1252//TRANSLIT', $value ?? '') ?: '';
    }
}
