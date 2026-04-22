<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['shop_id', 'sku', 'name', 'category', 'image_path', 'minimum_stock', 'unit_price', 'is_active'])]
class Part extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'minimum_stock' => 'integer',
            'unit_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    protected function currentStock(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes): int {
                if ($value !== null) {
                    return (int) $value;
                }

                return (int) ($attributes['current_stock'] ?? 0);
            },
        );
    }

    #[Scope]
    protected function forShop(Builder $query, Shop|int $shop): void
    {
        $shopId = $shop instanceof Shop ? $shop->id : $shop;

        $query->where('shop_id', $shopId);
    }

    #[Scope]
    protected function withCurrentStock(Builder $query): void
    {
        $stockTotals = StockMovement::query()
            ->selectRaw("
                part_id,
                COALESCE(SUM(
                    CASE
                        WHEN type = 'in' THEN quantity
                        WHEN type = 'out' THEN -quantity
                        ELSE quantity
                    END
                ), 0) AS current_stock
            ")
            ->groupBy('part_id');

        $query->leftJoinSub($stockTotals, 'stock_totals', function ($join): void {
            $join->on('parts.id', '=', 'stock_totals.part_id');
        })
            ->select('parts.*')
            ->selectRaw('COALESCE(stock_totals.current_stock, 0) as current_stock');
    }

    /**
     * @return BelongsTo<Shop, $this>
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * @return HasMany<StockMovement, $this>
     */
    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}

