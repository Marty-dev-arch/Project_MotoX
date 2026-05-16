<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

// Purpose: Represents one-time codes for password reset.
#[Fillable([
    'email',
    'otp_hash',
    'attempts',
    'expires_at',
    'consumed_at',
    'ip_address',
])]
class PasswordResetOtp extends Model
{
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }

    public function scopeActiveForEmail(Builder $query, string $email): void
    {
        $query
            ->where('email', strtolower(trim($email)))
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now());
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}

