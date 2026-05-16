<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

// Purpose: Represents application users and authentication data.
#[Fillable(['name', 'username', 'avatar_path', 'email', 'password', 'role', 'shop_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdminMechanic(): bool
    {
        return $this->role === 'admin';
    }

    public function shop(): HasOne
    {
        return $this->hasOne(Shop::class);
    }

    public function assignedShop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function workspaceShop(): ?Shop
    {
        return $this->assignedShop ?: $this->shop;
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function notificationsFeed(): HasMany
    {
        return $this->hasMany(SystemNotification::class);
    }
}
