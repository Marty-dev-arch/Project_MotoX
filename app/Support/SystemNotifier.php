<?php

namespace App\Support;

use App\Models\Part;
use App\Models\Shop;
use App\Models\SystemNotification;
use App\Models\User;

class SystemNotifier
{
    public static function notifyStockLevel(Part $part, float $currentStock): void
    {
        $shop = $part->shop;
        if (! $shop) {
            return;
        }

        $activeOutAlert = SystemNotification::query()
            ->where('shop_id', $shop->id)
            ->where('type', 'stock.out')
            ->where('data', 'like', '%"part_id":'.$part->id.'%')
            ->whereNull('read_at')
            ->latest('id')
            ->first();

        if ($activeOutAlert && $currentStock > 0) {
            $activeOutAlert->update(['read_at' => now()]);
            self::notifyShop(
                $shop,
                'stock.restocked',
                'Stock Restocked',
                sprintf('%s has been restocked and is now available.', $part->name),
                'success',
                ['part_id' => $part->id, 'current_stock' => $currentStock],
                false,
            );
        }

        if ((float) $currentStock <= 0.0) {
            self::notifyShop(
                $shop,
                'stock.out',
                'Out of Stock',
                sprintf('%s is out of stock. Restock is required before this part can be sold again.', $part->name),
                'danger',
                ['part_id' => $part->id, 'current_stock' => $currentStock, 'minimum_stock' => (float) $part->minimum_stock, 'restock_recommended' => true],
                true,
            );
            return;
        }

        if ((float) $currentStock < (float) $part->minimum_stock) {
            self::notifyShop(
                $shop,
                'stock.low',
                'Low Stock',
                sprintf('%s is running low (%.3f remaining, minimum %.3f). Restock support is recommended.', $part->name, $currentStock, (float) $part->minimum_stock),
                'warning',
                ['part_id' => $part->id, 'current_stock' => $currentStock, 'minimum_stock' => (float) $part->minimum_stock, 'restock_recommended' => true],
                true,
            );
        }
    }

    public static function notifyShop(
        Shop|int|null $shop,
        string $type,
        string $title,
        ?string $body,
        string $severity = 'info',
        array $data = [],
        bool $dedupeByUnreadType = false,
    ): ?SystemNotification {
        $shopId = $shop instanceof Shop ? $shop->id : (is_int($shop) ? $shop : null);
        if (! $shopId) {
            return null;
        }

        if ($dedupeByUnreadType) {
            $existingQuery = SystemNotification::query()
                ->where('shop_id', $shopId)
                ->where('type', $type)
                ->whereNull('read_at')
                ->latest('id');

            if (array_key_exists('part_id', $data)) {
                $existingQuery->where('data', 'like', '%"part_id":'.$data['part_id'].'%');
            }

            $existing = $existingQuery->first();

            if ($existing) {
                $existing->update([
                    'title' => $title,
                    'body' => $body,
                    'severity' => $severity,
                    'data' => $data,
                ]);

                return $existing;
            }
        }

        return SystemNotification::query()->create([
            'shop_id' => $shopId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'severity' => $severity,
            'data' => $data,
        ]);
    }

    public static function notifyUser(
        User|int $user,
        Shop|int|null $shop,
        string $type,
        string $title,
        ?string $body,
        string $severity = 'info',
        array $data = [],
        bool $dedupeByUnreadType = false,
    ): ?SystemNotification {
        $userId = $user instanceof User ? $user->id : $user;
        $shopId = $shop instanceof Shop ? $shop->id : (is_int($shop) ? $shop : null);

        if ($dedupeByUnreadType) {
            $existingQuery = SystemNotification::query()
                ->where('user_id', $userId)
                ->where('type', $type)
                ->whereNull('read_at')
                ->latest('id');

            if (array_key_exists('part_id', $data)) {
                $existingQuery->where('data', 'like', '%"part_id":'.$data['part_id'].'%');
            }

            $existing = $existingQuery->first();

            if ($existing) {
                $existing->update([
                    'title' => $title,
                    'body' => $body,
                    'severity' => $severity,
                    'data' => $data,
                ]);

                return $existing;
            }
        }

        return SystemNotification::query()->create([
            'shop_id' => $shopId,
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'severity' => $severity,
            'data' => $data,
        ]);
    }
}
