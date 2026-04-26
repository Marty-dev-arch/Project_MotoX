<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['shop_id', 'name', 'email', 'phone', 'address', 'notes'])]
class Customer extends Model
{
    use HasFactory;

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

    
    public function jobOrders(): HasMany
    {
        return $this->hasMany(JobOrder::class);
    }
}

