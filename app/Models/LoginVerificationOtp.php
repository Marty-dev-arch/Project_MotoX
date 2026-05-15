<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id',
    'email',
    'provider',
    'otp_hash',
    'attempts',
    'expires_at',
    'consumed_at',
    'ip_address',
])]
class LoginVerificationOtp extends Model
{
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }

    public function scopeActiveFor(Builder $query, int $userId, string $email): void
    {
        $query
            ->where('user_id', $userId)
            ->where('email', strtolower(trim($email)))
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now());
    }
}
