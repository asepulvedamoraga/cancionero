<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Repertoire;
use App\Services\PublicRepertoireAccessService;
use App\Services\RepertoireExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicRepertoireDownloadController extends Controller
{
    public function __invoke(Repertoire $repertoire, PublicRepertoireAccessService $access, RepertoireExportService $exporter): BinaryFileResponse|RedirectResponse
    {
        $access->ensurePublic($repertoire);
        abort_unless($repertoire->allow_public_download, 404);

        try {
            $export = $exporter->generate($repertoire);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['export' => $exception->getMessage()]);
        }

        return response()->download(Storage::disk('local')->path($export['path']), $export['name'], ['Content-Type' => 'application/pdf']);
    }
}
