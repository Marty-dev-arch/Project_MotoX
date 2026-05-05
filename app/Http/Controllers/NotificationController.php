<?php

namespace App\Http\Controllers;

use App\Models\SystemNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if(! $user, 401);

        $shopId = $user->workspaceShop()?->id;

        $notifications = $this->baseQuery($shopId, $user->id)
            ->whereNull('read_at')
            ->latest('id')
            ->take(20)
            ->get()
            ->map(fn (SystemNotification $notification): array => [
                'id' => $notification->id,
                'title' => $notification->title,
                'body' => $notification->body,
                'type' => $notification->type,
                'severity' => $notification->severity,
                'is_read' => $notification->read_at !== null,
                'created_at' => $notification->created_at?->toIso8601String(),
                'created_human' => $notification->created_at?->timezone('Asia/Manila')->format('M d, h:i A').' PHT',
            ])
            ->values();

        $unreadCount = $this->baseQuery($shopId, $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'items' => $notifications,
            'unread_count' => $unreadCount,
            'updated_at' => now('Asia/Manila')->toIso8601String(),
        ]);
    }

    public function markAllRead(Request $request): Response
    {
        $user = $request->user();
        abort_if(! $user, 401);

        $shopId = $user->workspaceShop()?->id;

        $this->baseQuery($shopId, $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->noContent();
    }

    public function destroy(Request $request, SystemNotification $notification): Response
    {
        $user = $request->user();
        abort_if(! $user, 401);

        $shopId = $user->workspaceShop()?->id;

        $allowed = $this->baseQuery($shopId, $user->id)
            ->whereKey($notification->id)
            ->exists();

        abort_if(! $allowed, 404);

        $notification->delete();

        return response()->noContent();
    }

    private function baseQuery(?int $shopId, int $userId)
    {
        return SystemNotification::query()
            ->where(function ($query) use ($shopId, $userId): void {
                $query->where('user_id', $userId);

                if ($shopId) {
                    $query->orWhere(function ($shopQuery) use ($shopId): void {
                        $shopQuery->where('shop_id', $shopId)->whereNull('user_id');
                    });
                }
            });
    }
}
