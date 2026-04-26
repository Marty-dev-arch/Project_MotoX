<?php

namespace App\Support;

use App\Models\Part;
use App\Models\Shop;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class InventoryMetrics
{
    public static function partsWithStockQuery(Shop|int $shop): Builder
    {
        return Part::query()->forShop($shop)->withCurrentStock();
    }

    /**
     * @param Collection<int, Part> $parts
     * @return array{totalSkus:int,lowStock:int,outOfStock:int,inventoryValue:float}
     */
    public static function summarizeParts(Collection $parts): array
    {
        $activeParts = $parts->where('is_active', true);

        $totalSkus = $parts->count();
        $lowStock = $activeParts->filter(
            fn (Part $part): bool => $part->current_stock < $part->minimum_stock
        )->count();
        $outOfStock = $activeParts->filter(
            fn (Part $part): bool => $part->current_stock <= 0
        )->count();
        $inventoryValue = $parts->sum(
            fn (Part $part): float => max(0, (int) $part->current_stock) * (float) $part->unit_price
        );

        return [
            'totalSkus' => $totalSkus,
            'lowStock' => $lowStock,
            'outOfStock' => $outOfStock,
            'inventoryValue' => round($inventoryValue, 2),
        ];
    }

    /**
     * @return array<int, array{day:string,label:string,in:int,out:int,adjust:int,net:int}>
     */
    public static function movementTrend(Shop $shop, int $days = 7): array
    {
        $days = max(2, $days);
        $from = now()->subDays($days - 1)->startOfDay();

        $rows = StockMovement::query()
            ->join('parts', 'parts.id', '=', 'stock_movements.part_id')
            ->where('parts.shop_id', $shop->id)
            ->where('stock_movements.moved_at', '>=', $from)
            ->selectRaw('DATE(stock_movements.moved_at) as day')
            ->selectRaw("COALESCE(SUM(CASE WHEN stock_movements.type = 'in' THEN ABS(stock_movements.quantity) ELSE 0 END), 0) as in_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN stock_movements.type = 'out' THEN ABS(stock_movements.quantity) ELSE 0 END), 0) as out_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN stock_movements.type = 'adjust' THEN stock_movements.quantity ELSE 0 END), 0) as adjust_total")
            ->groupByRaw('DATE(stock_movements.moved_at)')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $trend = [];

        for ($offset = 0; $offset < $days; $offset++) {
            $day = $from->copy()->addDays($offset);
            $key = $day->toDateString();
            $row = $rows->get($key);

            $in = (int) ($row?->in_total ?? 0);
            $out = (int) ($row?->out_total ?? 0);
            $adjust = (int) ($row?->adjust_total ?? 0);
            $net = $in - $out + $adjust;

            $trend[] = [
                'day' => $key,
                'label' => $day->format('D'),
                'in' => $in,
                'out' => $out,
                'adjust' => $adjust,
                'net' => $net,
            ];
        }

        return $trend;
    }

    /**
     * @param Collection<int, Part> $parts
     * @return array<int, array{category:string,count:int}>
     */
    public static function lowStockByCategory(Collection $parts): array
    {
        return $parts
            ->where('is_active', true)
            ->filter(fn (Part $part): bool => $part->current_stock < $part->minimum_stock)
            ->groupBy('category')
            ->map(fn (Collection $items, string $category): array => [
                'category' => $category,
                'count' => $items->count(),
            ])
            ->sortByDesc('count')
            ->values()
            ->all();
    }

    public static function formatCurrency(float $amount): string
    {
        return 'PHP '.number_format($amount, 2);
    }

    public static function formatMovementTime(Carbon|string $time): string
    {
        return Carbon::parse($time)->format('M d, h:i A');
    }
}

