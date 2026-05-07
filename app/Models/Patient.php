<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Patient extends Model
{
    protected $fillable = [
        'external_id',
        'name',
        'surname',
        'sex',
        'birth_date',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date:Y-m-d',
        ];
    }

    public static function normalizeLogin(string $value): string
    {
        return Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/\s+/', '')
            ->replaceMatches('/[^a-z0-9]/', '')
            ->toString();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
