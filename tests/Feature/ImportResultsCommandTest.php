<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ImportResultsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_command_persists_records_from_csv(): void
    {
        $csvPath = storage_path('app/test-results.csv');
        File::put($csvPath, implode(PHP_EOL, [
            'patientId;patientName;patientSurname;patientSex;patientBirthDate;orderId;testName;testValue;testReference',
            '1;Piotr;Kowalski;male;1983-04-12;1;Glukoza;4.2;70-99',
            '1;Piotr;Kowalski;male;1983-04-12;1;Insulina;7.4;3-17',
        ]));

        $this->artisan('results:import', ['file' => $csvPath])
            ->assertExitCode(0);

        $this->assertDatabaseHas('patients', [
            'external_id' => '1',
            'name' => 'Piotr',
            'surname' => 'Kowalski',
            'sex' => 'm',
        ]);

        $this->assertDatabaseHas('orders', [
            'external_id' => '1',
        ]);

        $this->assertDatabaseCount('test_results', 2);

        File::delete($csvPath);
    }
}
