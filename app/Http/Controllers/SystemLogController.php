<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsPageData;
use App\Models\Customer;
use App\Models\JobOrder;
use App\Models\Part;
use App\Models\StockMovement;
use App\Models\SystemLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemLogController extends Controller
{
    use BuildsPageData;

    public function index(Request $request): View
    {
        $shop = auth()->user()?->workspaceShop();
        abort_if($shop === null, 403, 'Shop profile not found.');

        $eventType = $this->normalizeEventType($request->query('event_type'));
        $dateTime = $this->normalizeDateTimeFilter($request->query('date_time'));
        $search = trim((string) $request->query('search', ''));

        $baseQuery = SystemLog::query()
            ->with([
                'subject' => function (MorphTo $morphTo): void {
                    $morphTo->morphWith([
                        JobOrder::class => ['customer'],
                        StockMovement::class => ['part'],
                    ]);
                },
            ])
            ->where('shop_id', $shop->id)
            ->where('action', 'not like', 'auth.%');

        $this->applySearchFilter($baseQuery, $search);
        $this->applyDateTimeFilter($baseQuery, $dateTime);

        $eventCountsQuery = clone $baseQuery;
        $eventCounts = [
            'total_logs' => (clone $eventCountsQuery)->count(),
            'created' => $this->applyEventTypeFilter(clone $eventCountsQuery, 'created')->count(),
            'updated' => $this->applyEventTypeFilter(clone $eventCountsQuery, 'updated')->count(),
            'deleted' => $this->applyEventTypeFilter(clone $eventCountsQuery, 'deleted')->count(),
            'stock' => $this->applyEventTypeFilter(clone $eventCountsQuery, 'stock')->count(),
        ];

        $logsQuery = $eventType === 'total_logs'
            ? $baseQuery
            : $this->applyEventTypeFilter($baseQuery, $eventType);

        $perPage = $dateTime === 'today'
            ? max(1, (clone $logsQuery)->count())
            : 50;

        $logs = $logsQuery
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('pages.logs', $this->buildPageData('logs', [
            'heading' => 'History Log',
            'subheading' => 'Full history of created, updated, deleted, and stock actions.',
            'logs' => $logs,
            'eventCounts' => $eventCounts,
            'filters' => [
                'event_type' => $eventType,
                'date_time' => $dateTime,
                'search' => $search,
            ],
        ]));
    }

    public function destroy(SystemLog $log): RedirectResponse
    {
        $this->authorizeLog($log);

        $log->delete();

        return redirect()
            ->route('logs')
            ->with('status', 'Log entry deleted successfully.')
            ->with('status_tone', 'danger');
    }

    public function destroyAll(Request $request): RedirectResponse
    {
        $shop = $request->user()?->workspaceShop();
        abort_if($shop === null, 403, 'Shop profile not found.');

        SystemLog::query()
            ->where('shop_id', $shop->id)
            ->delete();

        return redirect()
            ->route('logs')
            ->with('status', 'All log history deleted successfully.')
            ->with('status_tone', 'danger');
    }

    private function authorizeLog(SystemLog $log): void
    {
        $shop = auth()->user()?->workspaceShop();
        abort_if($shop === null, 403, 'Shop profile not found.');

        abort_if((int) $log->shop_id !== (int) $shop->id, 404);
    }

    private function normalizeEventType(mixed $value): string
    {
        $value = strtolower(trim((string) $value));

        return in_array($value, ['created', 'updated', 'deleted', 'stock'], true)
            ? $value
            : 'total_logs';
    }

    private function normalizeDateTimeFilter(mixed $value): string
    {
        $value = strtolower(trim((string) $value));

        return in_array($value, ['today', 'week', 'month', 'year'], true)
            ? $value
            : 'all';
    }

    private function applySearchFilter(Builder $query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $needle = '%'.str_replace(['%', '_'], ['\%', '\_'], $search).'%';

        $query->where(function (Builder $searchQuery) use ($needle): void {
            $searchQuery
                ->where('action', 'like', $needle)
                ->orWhere('description', 'like', $needle)
                ->orWhere('metadata', 'like', $needle)
                ->orWhereHasMorph('subject', [Customer::class], function (Builder $customerQuery) use ($needle): void {
                    $customerQuery
                        ->where('name', 'like', $needle)
                        ->orWhere('email', 'like', $needle)
                        ->orWhere('phone', 'like', $needle);
                })
                ->orWhereHasMorph('subject', [JobOrder::class], function (Builder $orderQuery) use ($needle): void {
                    $orderQuery
                        ->where('order_number', 'like', $needle)
                        ->orWhere('vehicle', 'like', $needle)
                        ->orWhere('concern', 'like', $needle)
                        ->orWhereHas('customer', function (Builder $customerQuery) use ($needle): void {
                            $customerQuery->where('name', 'like', $needle);
                        });
                })
                ->orWhereHasMorph('subject', [Part::class], function (Builder $partQuery) use ($needle): void {
                    $partQuery
                        ->where('name', 'like', $needle)
                        ->orWhere('sku', 'like', $needle)
                        ->orWhere('category', 'like', $needle);
                });
        });
    }

    private function applyDateTimeFilter(Builder $query, string $dateTime): void
    {
        $now = now('Asia/Manila');

        $bounds = match ($dateTime) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default => null,
        };

        if ($bounds) {
            $query->whereBetween('created_at', [
                $bounds[0]->copy()->utc(),
                $bounds[1]->copy()->utc(),
            ]);
        }
    }

    private function applyEventTypeFilter(Builder $query, string $eventType): Builder
    {
        return match ($eventType) {
            'created' => $query->where('action', 'like', '%.created'),
            'updated' => $query->where('action', 'like', '%.updated'),
            'deleted' => $query->where('action', 'like', '%.deleted'),
            'stock' => $query->where('action', 'like', 'stock.%'),
            default => $query,
        };
    }
}
