<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function store(Request $request, JwtService $jwtService): JsonResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'date_format:Y-m-d'],
        ]);

        $normalizedLogin = Patient::normalizeLogin($credentials['login']);
        $patient = Patient::query()->get()->first(function (Patient $patient) use ($normalizedLogin, $credentials): bool {
            return Patient::normalizeLogin($patient->name.$patient->surname) === $normalizedLogin
                && $patient->birth_date->format('Y-m-d') === $credentials['password'];
        });

        if (! $patient) {
            return response()->json(['message' => 'Invalid credentials.'], 404);
        }

        $token = $jwtService->issueToken($patient);

        return response()->json([
            'token' => $token['token'],
            'expiresAt' => $token['expires_at'],
        ]);
    }
}
