<?php

namespace Tests\Feature;

use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoPatientResultsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_creates_accessible_patient_data(): void
    {
        $this->seed(\Database\Seeders\DemoPatientResultsSeeder::class);

        $this->assertDatabaseCount('patients', 2);
        $this->assertDatabaseCount('orders', 3);
        $this->assertDatabaseCount('test_results', 6);

        $this->assertTrue(
            Patient::query()->get()->contains(fn (Patient $patient) => $patient->name === 'Piotr' && $patient->surname === 'Kowalski')
        );
    }
}
