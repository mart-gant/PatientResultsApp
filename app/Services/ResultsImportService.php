<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Patient;
use App\Models\TestResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

class ResultsImportService
{
    private const EXPECTED_COLUMNS = [
        'patientId',
        'patientName',
        'patientSurname',
        'patientSex',
        'patientBirthDate',
        'orderId',
        'testName',
        'testValue',
        'testReference',
    ];

    public function import(string $path): array
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new InvalidArgumentException("CSV file is not readable: {$path}");
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new RuntimeException("Unable to open CSV file: {$path}");
        }

        $summary = [
            'patients' => 0,
            'orders' => 0,
            'results' => 0,
            'errors' => 0,
        ];

        $header = $this->readRow($handle);
        if ($header === null) {
            throw new RuntimeException('CSV file is empty.');
        }

        $header = array_map([$this, 'normalizeHeader'], $header);
        if ($header !== self::EXPECTED_COLUMNS) {
            throw new RuntimeException('CSV header does not match expected format.');
        }

        $line = 1;
        while (($row = $this->readRow($handle)) !== null) {
            $line++;

            if ($this->isEmptyRow($row)) {
                continue;
            }

            if (count($row) !== count(self::EXPECTED_COLUMNS)) {
                $summary['errors']++;
                Log::channel('import')->warning('Skipping malformed CSV row.', [
                    'line' => $line,
                    'row' => $row,
                    'reason' => 'invalid-column-count',
                ]);
                continue;
            }

            $record = array_combine(self::EXPECTED_COLUMNS, $row);
            if ($record === false || ! $this->isComplete($record)) {
                $summary['errors']++;
                Log::channel('import')->warning('Skipping incomplete CSV row.', [
                    'line' => $line,
                    'row' => $row,
                    'reason' => 'missing-required-field',
                ]);
                continue;
            }

            DB::transaction(function () use ($record, &$summary, $line): void {
                $patient = Patient::updateOrCreate(
                    ['external_id' => (string) $record['patientId']],
                    [
                        'name' => trim((string) $record['patientName']),
                        'surname' => trim((string) $record['patientSurname']),
                        'sex' => $this->normalizeSex((string) $record['patientSex']),
                        'birth_date' => trim((string) $record['patientBirthDate']),
                    ]
                );

                $order = Order::updateOrCreate(
                    [
                        'patient_id' => $patient->id,
                        'external_id' => (string) $record['orderId'],
                    ],
                    []
                );

                TestResult::updateOrCreate(
                    [
                        'order_id' => $order->id,
                        'name' => trim((string) $record['testName']),
                    ],
                    [
                        'value' => trim((string) $record['testValue']),
                        'reference' => trim((string) $record['testReference']),
                    ]
                );

                $summary['patients'] = Patient::count();
                $summary['orders'] = Order::count();
                $summary['results'] = TestResult::count();

                Log::channel('import')->info('Imported CSV row.', [
                    'line' => $line,
                    'patientId' => $record['patientId'],
                    'orderId' => $record['orderId'],
                    'testName' => $record['testName'],
                ]);
            });
        }

        fclose($handle);

        return $summary;
    }

    private function readRow($handle): ?array
    {
        $row = fgetcsv($handle, 0, ';', '"', '\\');

        if ($row === false) {
            return null;
        }

        if (isset($row[0])) {
            $row[0] = preg_replace('/^\x{FEFF}/u', '', (string) $row[0]) ?? (string) $row[0];
        }

        return array_map(
            static fn ($value) => is_string($value) ? trim($value) : '',
            $row
        );
    }

    private function normalizeHeader(string $value): string
    {
        return trim($value);
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== '') {
                return false;
            }
        }

        return true;
    }

    private function isComplete(array $record): bool
    {
        foreach (['patientId', 'patientName', 'patientSurname', 'patientSex', 'patientBirthDate', 'orderId', 'testName', 'testValue'] as $field) {
            if (trim((string) $record[$field]) === '') {
                return false;
            }
        }

        return true;
    }

    private function normalizeSex(string $value): string
    {
        $normalized = strtolower(trim($value));

        return match ($normalized) {
            'm', 'male', 'man' => 'm',
            'f', 'female', 'woman' => 'f',
            default => $normalized ?: 'u',
        };
    }
}
