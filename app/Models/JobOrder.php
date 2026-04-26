<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'shop_id',
    'customer_id',
    'order_number',
    'vehicle',
    'concern',
    'status',
    'estimated_cost',
    'scheduled_for',
    'completed_at',
    'notes',
])]
class JobOrder extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    
    protected function casts(): array
    {
        return [
            'estimated_cost' => 'decimal:2',
            'scheduled_for' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    
    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_IN_PROGRESS,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
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
}

