<?php

namespace App\Console\Commands;

use App\Services\ResultsImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportResultsCommand extends Command
{
    protected $signature = 'results:import {file : Path to the CSV file}';

    protected $description = 'Import patient results from a CSV file.';

    public function handle(ResultsImportService $importService): int
    {
        $file = (string) $this->argument('file');

        try {
            $summary = $importService->import($file);

            $this->info(sprintf(
                'Imported patients: %d, orders: %d, results: %d, errors: %d',
                $summary['patients'],
                $summary['orders'],
                $summary['results'],
                $summary['errors'],
            ));

            Log::channel('import')->info('Import completed.', $summary + ['file' => $file]);

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());
            Log::channel('import')->error('Import failed.', [
                'file' => $file,
                'error' => $exception->getMessage(),
            ]);

            return self::FAILURE;
        }
    }
}
