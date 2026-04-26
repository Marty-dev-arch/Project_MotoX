<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'shop_id',
    'customer_id',
    'vehicle_name',
    'plate_number',
    'vin',
    'engine_number',
    'year_model',
    'color',
    'notes',
    'is_active',
])]
class Vehicle extends Model
{
    use HasFactory;

    
    protected function casts(): array
    {
        return [
            'year_model' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    #[Scope]
    protected function forShop(Builder $query, Shop|int $shop): void
    {
        $shopId = $shop instanceof Shop ? $shop->id : $shop;

        $query->where('shop_id', $shopId);
    }

    
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    
    public function jobOrders(): HasMany
    {
        return $this->hasMany(JobOrder::class);
    }
}

