<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Patient;
use App\Models\TestResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_results_flow_returns_patient_data(): void
    {
        $patient = Patient::create([
            'external_id' => '10',
            'name' => 'John',
            'surname' => 'Smith',
            'sex' => 'm',
            'birth_date' => '2021-01-01',
        ]);

        $order = Order::create([
            'patient_id' => $patient->id,
            'external_id' => '20',
        ]);

        TestResult::create([
            'order_id' => $order->id,
            'name' => 'foo',
            'value' => '1',
            'reference' => '1-2',
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'login' => 'JohnSmith',
            'password' => '2021-01-01',
        ]);

        $loginResponse->assertOk()->assertJsonStructure(['token', 'expiresAt']);

        $resultsResponse = $this->getJson('/api/results', [
            'Authorization' => 'Bearer '.$loginResponse->json('token'),
        ]);

        $resultsResponse->assertOk()->assertJson([
            'patient' => [
                'id' => $patient->id,
                'name' => 'John',
                'surname' => 'Smith',
                'sex' => 'm',
                'birthDate' => '2021-01-01',
            ],
        ]);
    }

    public function test_results_endpoint_requires_token(): void
    {
        $this->getJson('/api/results')->assertStatus(401);
    }
}
