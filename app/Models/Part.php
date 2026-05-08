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

#[Fillable([
    'shop_id',
    'sku',
    'name',
    'category',
    'notes',
    'image_path',
    'stock_mode',
    'unit_label',
    'pieces_per_box',
    'allow_fractional_quantity',
    'minimum_stock',
    'unit_price',
    'unit_price_per_box',
    'unit_price_per_piece',
    'unit_price_basis',
    'is_active',
])]
class Part extends Model
{
    use HasFactory;

    
    protected function casts(): array
    {
        return [
            'minimum_stock' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'unit_price_per_box' => 'decimal:2',
            'unit_price_per_piece' => 'decimal:2',
            'unit_price_basis' => 'string',
            'is_active' => 'boolean',
            'pieces_per_box' => 'decimal:3',
            'allow_fractional_quantity' => 'boolean',
        ];
    }

    protected function currentStock(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes): float {
                if ($value !== null) {
                    return (float) $value;
                }

                return (float) ($attributes['current_stock'] ?? 0);
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
                        WHEN type = 'opening' THEN quantity
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

    
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    
    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function usesBoxConversion(): bool
    {
        return $this->stock_mode === 'box_piece' && (float) ($this->pieces_per_box ?? 0) > 0;
    }

    public function defaultUnitLabel(): string
    {
        if (trim((string) $this->unit_label) !== '') {
            return $this->unit_label;
        }

        return 'box';
    }
}
