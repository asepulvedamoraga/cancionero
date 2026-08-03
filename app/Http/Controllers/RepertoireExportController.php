<?php

namespace App\Http\Controllers;

use App\Models\Repertoire;
use App\Services\RepertoireExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RepertoireExportController extends Controller
{
    public function __invoke(Repertoire $repertoire, RepertoireExportService $exporter): BinaryFileResponse|RedirectResponse
    {
        Gate::authorize('export', $repertoire);

        try {
            $export = $exporter->generate($repertoire);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['export' => $exception->getMessage()]);
        }

        return response()->download(Storage::disk('local')->path($export['path']), $export['name'], ['Content-Type' => 'application/pdf']);
    }
}
