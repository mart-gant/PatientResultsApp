<?php

namespace App\Http\Middleware;

use App\Models\Patient;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthenticate
{
    public function __construct(private readonly JwtService $jwtService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        try {
            $patientId = $this->jwtService->resolvePatientId($token);
            $patient = Patient::query()->find($patientId);
        } catch (\Throwable) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        if (! $patient) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $request->attributes->set('jwt_patient', $patient);

        return $next($request);
    }
}
