<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResultsController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $patient = $request->attributes->get('jwt_patient');

        if (! $patient instanceof Patient) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $patient->load(['orders.results']);

        if ($patient->orders->isEmpty() || $patient->orders->flatMap->results->isEmpty()) {
            return response()->json(['message' => 'Results not found.'], 404);
        }

        return response()->json([
            'patient' => [
                'id' => $patient->id,
                'name' => $patient->name,
                'surname' => $patient->surname,
                'sex' => $patient->sex,
                'birthDate' => $patient->birth_date->format('Y-m-d'),
            ],
            'orders' => $patient->orders->map(fn ($order) => [
                'orderId' => (string) $order->external_id,
                'results' => $order->results->map(fn ($result) => [
                    'name' => $result->name,
                    'value' => $result->value,
                    'reference' => $result->reference,
                ])->values(),
            ])->values(),
        ]);
    }
}
