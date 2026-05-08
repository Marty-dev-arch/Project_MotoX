<?php

namespace App\Support;

use App\Models\Shop;
use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SystemLogger
{
    public static function record(
        string $action,
        string $description,
        ?Model $subject = null,
        array $metadata = [],
        Shop|int|null $shop = null,
        User|int|null $user = null,
    ): ?SystemLog {
        $currentUser = auth()->user();
        $shopId = self::resolveShopId($shop, $subject, $currentUser);
        $userId = self::resolveUserId($user, $currentUser);

        if (! $shopId && ! $userId && ! $subject) {
            return null;
        }

        return SystemLog::query()->create([
            'shop_id' => $shopId,
            'user_id' => $userId,
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'description' => Str::limit($description, 490, '...'),
            'metadata' => $metadata ?: null,
            'ip_address' => null,
            'user_agent' => null,
        ]);
    }

    private static function resolveShopId(Shop|int|null $shop, ?Model $subject, ?User $currentUser): ?int
    {
        if ($shop instanceof Shop) {
            return $shop->id;
        }

        if (is_int($shop)) {
            return $shop;
        }

        if ($subject && isset($subject->shop_id)) {
            return (int) $subject->shop_id;
        }

        if ($subject && method_exists($subject, 'part') && $subject->part) {
            return (int) $subject->part->shop_id;
        }

        return $currentUser?->workspaceShop()?->id;
    }

    private static function resolveUserId(User|int|null $user, ?User $currentUser): ?int
    {
        if ($user instanceof User) {
            return $user->id;
        }

        if (is_int($user)) {
            return $user;
        }

        return $currentUser?->id;
    }
}
