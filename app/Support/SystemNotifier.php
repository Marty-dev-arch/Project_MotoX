<?php

namespace App\Support;

use App\Models\Part;
use App\Models\Shop;
use App\Models\SystemNotification;
use App\Models\User;
use App\Models\JobOrder;

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
        }

        if ((float) $currentStock <= 0.0) {
            self::notifyShop(
                $shop,
                'stock.out',
                'Out of Stock',
                sprintf('%s is out of stock. you required to Restock before this part can be sold again.', $part->name),
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
                sprintf('%s is running low (%.3f remaining, minimum %.3f). required to Restock.', $part->name, $currentStock, (float) $part->minimum_stock),
                'warning',
                ['part_id' => $part->id, 'current_stock' => $currentStock, 'minimum_stock' => (float) $part->minimum_stock, 'restock_recommended' => true],
                true,
            );
        }
    }

    public static function notifyBillingUpdated(Shop $shop, JobOrder $order, string $action = 'updated'): void
    {
        if (! $shop->notify_billing_updates || (float) $order->estimated_cost <= 0) {
            return;
        }

        $customer = $order->customer?->name ?? 'Walk-in Customer';
        $amount = 'PHP '.number_format((float) $order->estimated_cost, 2);
        $isCreated = $action === 'created';

        self::notifyShop(
            $shop,
            $isCreated ? 'billing.created' : 'billing.updated',
            $isCreated ? 'New Billing Record' : 'Billing Updated',
            sprintf('%s now has %s for %s.', $order->order_number, $amount, $customer),
            'success',
            [
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'amount' => (float) $order->estimated_cost,
            ],
        );
    }

    public static function notifyTopBillingCustomer(Shop $shop, ?array $topCustomer): void
    {
        if (! $shop->notify_billing_updates || empty($topCustomer['customer_id'])) {
            return;
        }

        self::notifyShop(
            $shop,
            'report.top_customer',
            'Top Billing Customer',
            sprintf(
                '%s is now the top billing customer with PHP %s.',
                $topCustomer['name'] ?? 'Customer',
                number_format((float) ($topCustomer['total_billed'] ?? 0), 2),
            ),
            'info',
            [
                'customer_id' => (int) $topCustomer['customer_id'],
                'total_billed' => (float) ($topCustomer['total_billed'] ?? 0),
                'jobs' => (int) ($topCustomer['jobs'] ?? 0),
            ],
            true,
        );
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
