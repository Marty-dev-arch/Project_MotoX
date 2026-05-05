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

    public static function summarizeParts(Collection $parts): array
    {
        $activeParts = $parts->where('is_active', true);

        $totalSkus = $parts->count();
        $lowStock = $activeParts->filter(
            fn (Part $part): bool => $part->current_stock > 0 && $part->current_stock < $part->minimum_stock
        )->count();
        $outOfStock = $activeParts->filter(
            fn (Part $part): bool => (float) $part->current_stock <= 0
        )->count();
        $inventoryValue = $parts->sum(
            function (Part $part): float {
                $stock = max(0, (float) $part->current_stock);
                $unitPrice = (float) $part->unit_price;

                if ($part->usesBoxConversion()) {
                    $conversion = max(0.001, (float) $part->pieces_per_box);
                    $unitPrice = $unitPrice / $conversion;
                }

                return $stock * $unitPrice;
            }
        );

        return [
            'totalSkus' => $totalSkus,
            'lowStock' => $lowStock,
            'outOfStock' => $outOfStock,
            'inventoryValue' => round($inventoryValue, 2),
        ];
    }

    public static function movementTrend(Shop $shop, int $days = 7): array
    {
        $days = max(2, $days);
        $from = now('Asia/Manila')->subDays($days - 1)->startOfDay();
        $fromUtc = $from->copy()->utc();

        $rows = StockMovement::query()
            ->join('parts', 'parts.id', '=', 'stock_movements.part_id')
            ->where('parts.shop_id', $shop->id)
            ->where('stock_movements.moved_at', '>=', $fromUtc)
            ->select('stock_movements.type', 'stock_movements.quantity', 'stock_movements.moved_at')
            ->orderBy('stock_movements.moved_at')
            ->get()
            ->groupBy(fn (StockMovement $movement): string => $movement->moved_at->timezone('Asia/Manila')->toDateString())
            ->map(fn (Collection $dayRows): array => [
                'in_total' => $dayRows
                    ->where('type', StockMovement::TYPE_IN)
                    ->sum(fn (StockMovement $movement): float => abs((float) $movement->quantity)),
                'out_total' => $dayRows
                    ->where('type', StockMovement::TYPE_OUT)
                    ->sum(fn (StockMovement $movement): float => abs((float) $movement->quantity)),
                'adjust_total' => $dayRows
                    ->where('type', StockMovement::TYPE_ADJUST)
                    ->sum(fn (StockMovement $movement): float => (float) $movement->quantity),
            ]);

        $trend = [];

        for ($offset = 0; $offset < $days; $offset++) {
            $day = $from->copy()->addDays($offset);
            $key = $day->toDateString();
            $row = $rows->get($key);

            $in = (float) ($row['in_total'] ?? 0);
            $out = (float) ($row['out_total'] ?? 0);
            $adjust = (float) ($row['adjust_total'] ?? 0);
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

    public static function movementTrendForMonth(Shop $shop, int $year, int $month): array
    {
        $year = min(2100, max(2000, $year));
        $month = min(12, max(1, $month));
        $now = now('Asia/Manila');
        $start = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Manila')->startOfMonth();
        $end = $start->isSameMonth($now)
            ? $now->copy()->endOfDay()
            : $start->copy()->endOfMonth();

        $rows = StockMovement::query()
            ->join('parts', 'parts.id', '=', 'stock_movements.part_id')
            ->where('parts.shop_id', $shop->id)
            ->whereBetween('stock_movements.moved_at', [$start->copy()->utc(), $end->copy()->utc()])
            ->select('stock_movements.type', 'stock_movements.quantity', 'stock_movements.moved_at')
            ->orderBy('stock_movements.moved_at')
            ->get()
            ->groupBy(fn (StockMovement $movement): string => $movement->moved_at->timezone('Asia/Manila')->toDateString())
            ->map(fn (Collection $dayRows): array => [
                'in_total' => $dayRows
                    ->where('type', StockMovement::TYPE_IN)
                    ->sum(fn (StockMovement $movement): float => abs((float) $movement->quantity)),
                'out_total' => $dayRows
                    ->where('type', StockMovement::TYPE_OUT)
                    ->sum(fn (StockMovement $movement): float => abs((float) $movement->quantity)),
                'adjust_total' => $dayRows
                    ->where('type', StockMovement::TYPE_ADJUST)
                    ->sum(fn (StockMovement $movement): float => (float) $movement->quantity),
            ]);

        $trend = [];
        $cursor = $start->copy();

        while ($cursor->lessThanOrEqualTo($end)) {
            $key = $cursor->toDateString();
            $row = $rows->get($key);
            $in = (float) ($row['in_total'] ?? 0);
            $out = (float) ($row['out_total'] ?? 0);
            $adjust = (float) ($row['adjust_total'] ?? 0);

            $trend[] = [
                'day' => $key,
                'label' => $cursor->format('M j'),
                'date_label' => $cursor->format('F j, Y, l'),
                'day_name' => $cursor->format('l'),
                'in' => $in,
                'out' => $out,
                'adjust' => $adjust,
                'net' => $in - $out + $adjust,
            ];

            $cursor->addDay();
        }

        return $trend;
    }

    public static function movementTrendForMonths(Shop $shop, int $months = 3): array
    {
        $months = in_array($months, [3, 6, 12], true) ? $months : 3;
        $now = now('Asia/Manila');
        $start = $now->copy()->subMonths($months - 1)->startOfMonth();
        $end = $now->copy()->endOfDay();

        $rows = StockMovement::query()
            ->join('parts', 'parts.id', '=', 'stock_movements.part_id')
            ->where('parts.shop_id', $shop->id)
            ->whereBetween('stock_movements.moved_at', [$start->copy()->utc(), $end->copy()->utc()])
            ->select('stock_movements.type', 'stock_movements.quantity', 'stock_movements.moved_at')
            ->orderBy('stock_movements.moved_at')
            ->get()
            ->groupBy(fn (StockMovement $movement): string => $movement->moved_at->timezone('Asia/Manila')->format('Y-m'))
            ->map(fn (Collection $monthRows): array => [
                'in_total' => $monthRows
                    ->where('type', StockMovement::TYPE_IN)
                    ->sum(fn (StockMovement $movement): float => abs((float) $movement->quantity)),
                'out_total' => $monthRows
                    ->where('type', StockMovement::TYPE_OUT)
                    ->sum(fn (StockMovement $movement): float => abs((float) $movement->quantity)),
                'adjust_total' => $monthRows
                    ->where('type', StockMovement::TYPE_ADJUST)
                    ->sum(fn (StockMovement $movement): float => (float) $movement->quantity),
            ]);

        return collect(range($months - 1, 0))
            ->map(function (int $offset) use ($rows, $now): array {
                $month = $now->copy()->subMonths($offset)->startOfMonth();
                $monthEnd = $month->isSameMonth($now)
                    ? $now->copy()
                    : $month->copy()->endOfMonth();
                $key = $month->format('Y-m');
                $row = $rows->get($key);
                $in = (float) ($row['in_total'] ?? 0);
                $out = (float) ($row['out_total'] ?? 0);
                $adjust = (float) ($row['adjust_total'] ?? 0);

                return [
                    'day' => $monthEnd->toDateString(),
                    'label' => $month->format('M'),
                    'date_label' => $monthEnd->format('F j, Y, l'),
                    'in' => $in,
                    'out' => $out,
                    'adjust' => $adjust,
                    'net' => $in - $out + $adjust,
                ];
            })
            ->values()
            ->all();
    }

    public static function lowStockByCategory(Collection $parts): array
    {
        return $parts
            ->where('is_active', true)
            ->filter(fn (Part $part): bool => $part->current_stock > 0 && $part->current_stock < $part->minimum_stock)
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
        return Carbon::parse($time)->timezone('Asia/Manila')->format('M d, h:i A').' PHT';
    }
}
