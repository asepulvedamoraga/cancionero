<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupRepertoireExports extends Command
{
    protected $signature = 'repertoire:cleanup-exports {--hours= : Antigüedad mínima en horas}';

    protected $description = 'Elimina exportaciones temporales de repertorios vencidas';

    public function handle(): int
    {
        $hours = max(1, (int) ($this->option('hours') ?: config('cancionero.export.temporary_file_hours', 24)));
        $threshold = now()->subHours($hours)->timestamp;
        $deleted = 0;
        foreach (Storage::disk('local')->allFiles('exports') as $path) {
            if (Storage::disk('local')->lastModified($path) < $threshold && Storage::disk('local')->delete($path)) {
                $deleted++;
            }
        }
        $this->info("Se eliminaron {$deleted} exportaciones temporales.");

        return self::SUCCESS;
    }
}
