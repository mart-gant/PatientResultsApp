<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Patient;
use App\Models\TestResult;
use Illuminate\Database\Seeder;

class DemoPatientResultsSeeder extends Seeder
{
    public function run(): void
    {
        $patients = [
            [
                'external_id' => '1001',
                'name' => 'Piotr',
                'surname' => 'Kowalski',
                'sex' => 'm',
                'birth_date' => '1983-04-12',
                'orders' => [
                    [
                        'external_id' => '2001',
                        'results' => [
                            ['name' => 'Glukoza', 'value' => '4.2', 'reference' => '70-99'],
                            ['name' => 'Insulina', 'value' => '7.4', 'reference' => '3.0-17.0'],
                        ],
                    ],
                    [
                        'external_id' => '2002',
                        'results' => [
                            ['name' => 'FT3', 'value' => '4.00', 'reference' => '2.30-4.20'],
                            ['name' => 'TSH', 'value' => '334.000 uIU/ml', 'reference' => '0.550-4.780'],
                        ],
                    ],
                ],
            ],
            [
                'external_id' => '1002',
                'name' => 'Anna',
                'surname' => 'Jablonska',
                'sex' => 'f',
                'birth_date' => '2002-12-12',
                'orders' => [
                    [
                        'external_id' => '3001',
                        'results' => [
                            ['name' => 'Cholesterol LDL', 'value' => '42', 'reference' => '<70'],
                            ['name' => 'Azotyny', 'value' => 'nieobecne', 'reference' => 'nieobecne'],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($patients as $patientData) {
            $patient = Patient::updateOrCreate(
                ['external_id' => $patientData['external_id']],
                collect($patientData)->only(['name', 'surname', 'sex', 'birth_date'])->all()
            );

            foreach ($patientData['orders'] as $orderData) {
                $order = Order::updateOrCreate(
                    [
                        'patient_id' => $patient->id,
                        'external_id' => $orderData['external_id'],
                    ],
                    []
                );

                foreach ($orderData['results'] as $resultData) {
                    TestResult::updateOrCreate(
                        [
                            'order_id' => $order->id,
                            'name' => $resultData['name'],
                        ],
                        [
                            'value' => $resultData['value'],
                            'reference' => $resultData['reference'],
                        ]
                    );
                }
            }
        }
    }
}
