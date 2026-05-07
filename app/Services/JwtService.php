<?php

namespace App\Services;

use App\Models\Patient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use RuntimeException;

class JwtService
{
    public function issueToken(Patient $patient): array
    {
        $issuedAt = now()->timestamp;
        $expiresAt = now()->addMinutes((int) env('JWT_TTL_MINUTES', 120))->timestamp;

        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT',
        ], JSON_UNESCAPED_SLASHES));

        $payload = $this->base64UrlEncode(json_encode([
            'iss' => config('app.url'),
            'sub' => (string) $patient->id,
            'iat' => $issuedAt,
            'exp' => $expiresAt,
        ], JSON_UNESCAPED_SLASHES));

        return [
            'token' => $header.'.'.$payload.'.'.$this->sign($header.'.'.$payload),
            'expires_at' => Carbon::createFromTimestamp($expiresAt)->toIso8601String(),
        ];
    }

    public function resolvePatientId(string $token): int
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new RuntimeException('Invalid token format.');
        }

        [$header, $payload, $signature] = $parts;

        if (! hash_equals($this->sign($header.'.'.$payload), $signature)) {
            throw new RuntimeException('Invalid token signature.');
        }

        $payloadData = json_decode($this->base64UrlDecode($payload), true, flags: JSON_THROW_ON_ERROR);
        if (($payloadData['exp'] ?? 0) < now()->timestamp) {
            throw new RuntimeException('Token expired.');
        }

        return (int) ($payloadData['sub'] ?? 0);
    }

    private function sign(string $value): string
    {
        return $this->base64UrlEncode(hash_hmac('sha256', $value, $this->resolveSigningKey(), true));
    }

    private function resolveSigningKey(): string
    {
        $appKey = (string) config('app.key');

        if (Str::startsWith($appKey, 'base64:')) {
            $decoded = base64_decode(Str::after($appKey, 'base64:'), true);
            if ($decoded !== false) {
                return $decoded;
            }
        }

        return $appKey;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        if ($decoded === false) {
            throw new RuntimeException('Invalid base64 payload.');
        }

        return $decoded;
    }
}
