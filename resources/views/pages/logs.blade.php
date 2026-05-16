@extends('layouts.app')


{{-- Purpose: Renders the system logs page. --}}
@section('content')
    <section class="logs-history-page space-y-6">
        @if (session('status'))
            <div class="auth-alert auth-alert-{{ session('status_tone', 'success') }}">
                <p class="font-semibold">{{ session('status') }}</p>
            </div>
        @endif

        @php
            $filters = $filters ?? ['event_type' => 'total_logs', 'date_time' => 'all', 'search' => ''];
            $eventCounts = $eventCounts ?? ['total_logs' => $logs->total(), 'created' => 0, 'updated' => 0, 'deleted' => 0, 'stock' => 0];
            $eventOptions = [
                'total_logs' => 'Total logs',
                'created' => 'Created',
                'updated' => 'Updated',
                'deleted' => 'Deleted',
                'stock' => 'Stock',
            ];
            $dateOptions = [
                'all' => 'All date and time',
                'today' => 'Today',
                'week' => 'This week',
                'month' => 'This month',
                'year' => 'This year',
            ];
        @endphp

        <div class="logs-history-hero">
            <div class="min-w-0">
                <h1 class="logs-history-title">{{ $heading }}</h1>
                <p class="logs-history-copy">{{ $subheading }}</p>
            </div>

            <button
                type="button"
                class="logs-history-danger-button"
                data-open-modal="delete-all-logs-modal"
                @disabled($logs->total() === 0)
            >
                <x-icon name="trash" class="h-4 w-4" />
                <span>Delete All History</span>
            </button>
        </div>

        <section class="logs-history-shell" data-logs-shell>
            <form method="GET" action="{{ route('logs') }}" class="logs-history-filterbar" data-logs-filter-form>
                <label class="logs-history-search">
                    <x-icon name="search" class="h-4 w-4" />
                    <input
                        type="search"
                        name="search"
                        value="{{ $filters['search'] }}"
                        placeholder="Search history, customer, job order, part..."
                        autocomplete="off"
                        data-logs-live-search
                    >
                </label>

                <label class="logs-history-select">
                    <span>Event type</span>
                    <select name="event_type" data-logs-filter-select>
                        @foreach ($eventOptions as $value => $label)
                            <option value="{{ $value }}" @selected($filters['event_type'] === $value)>
                                {{ $label }} ({{ number_format($eventCounts[$value] ?? 0) }})
                            </option>
                        @endforeach
                    </select>
                </label>
                <input type="hidden" name="date_time" value="{{ $filters['date_time'] }}">
            </form>

            <div class="logs-history-result-line">
                <span>Showing {{ number_format($logs->total()) }} {{ $logs->total() === 1 ? 'history entry' : 'history entries' }}</span>
                <div class="logs-history-date-menu-shell">
                    <button type="button" class="logs-history-date-button" data-logs-date-trigger aria-expanded="false">
                        <x-icon name="calendar" class="h-4 w-4" />
                        <span>{{ $dateOptions[$filters['date_time']] ?? 'All date and time' }}</span>
                        <x-icon name="chevron-down" class="h-4 w-4" />
                    </button>
                    <div class="logs-history-date-menu hidden" data-logs-date-menu>
                        @foreach ($dateOptions as $value => $label)
                            <a
                                href="{{ route('logs', array_filter(['search' => $filters['search'], 'event_type' => $filters['event_type'], 'date_time' => $value !== 'all' ? $value : null])) }}"
                                class="{{ $filters['date_time'] === $value ? 'logs-history-date-option-active' : '' }}"
                            >
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="logs-history-list" aria-label="System history log">
                
                @forelse ($logs as $log)
                    @php
                        $normalizedAction = strtolower((string) $log->action);
                        $actionTone = str_contains($normalizedAction, 'deleted') || str_contains($normalizedAction, 'out')
                            ? 'danger'
                            : (str_contains($normalizedAction, 'updated') ? 'warning' : 'success');
                        $actionLabel = match (true) {
                            $normalizedAction === 'part.created' => 'Added new part',
                            $normalizedAction === 'part.updated' => 'Updated part',
                            $normalizedAction === 'part.deleted' => 'Deleted part',
                            str_starts_with($normalizedAction, 'stock.') => 'Stock movement',
                            $normalizedAction === 'job_order.created' => 'Created job order',
                            $normalizedAction === 'job_order.updated' => 'Updated job order',
                            $normalizedAction === 'job_order.deleted' => 'Deleted job order',
                            $normalizedAction === 'customer.created' => 'Created customer',
                            $normalizedAction === 'customer.updated' => 'Updated customer',
                            $normalizedAction === 'customer.deleted' => 'Deleted customer',
                            default => str_replace(['.', '_'], ' ', ucfirst($log->action)),
                        };
                        $actionInitial = strtoupper(mb_substr($actionLabel, 0, 1));
                        $subject = $log->subject;
                        $snapshot = $log->metadata['snapshot'] ?? [];
                        $snapshot = is_array($snapshot) ? $snapshot : [];
                        $subjectType = $subject ? class_basename($subject) : 'System';
                        $recordName = match (true) {
                            $subject instanceof \App\Models\Customer => $subject->name,
                            $subject instanceof \App\Models\JobOrder => 'Job Order # '.$subject->order_number,
                            $subject instanceof \App\Models\Part => $subject->name,
                            $subject instanceof \App\Models\StockMovement => $subject->part?->name ?? ($log->metadata['part_name'] ?? ''),
                            isset($snapshot['name']) => (string) $snapshot['name'],
                            isset($snapshot['order_number']) => 'Job Order # '.(string) $snapshot['order_number'],
                            isset($log->metadata['part_name']) => (string) $log->metadata['part_name'],
                            isset($snapshot['part_name']) => (string) $snapshot['part_name'],
                            isset($snapshot['sku']) => (string) ($snapshot['name'] ?? $snapshot['sku']),
                            default => '',
                        };
                        $recordDetail = match (true) {
                            $subject instanceof \App\Models\Customer => $subject->email ?: ($subject->phone ?: 'Customer record'),
                            $subject instanceof \App\Models\JobOrder => ($subject->customer?->name ?? 'Walk-in Customer').' - '.$subject->vehicle,
                            $subject instanceof \App\Models\Part => trim(($subject->sku ?: 'Part').' - '.($subject->category ?: 'Inventory')),
                            $subject instanceof \App\Models\StockMovement => trim((string) (($subject->part?->sku ?? $log->metadata['part_sku'] ?? 'Part').' - '.($subject->part?->category ?? $log->metadata['part_category'] ?? 'Inventory'))),
                            isset($snapshot['email']) => (string) ($snapshot['email'] ?: ($snapshot['phone'] ?? 'Customer record')),
                            isset($snapshot['vehicle']) => trim((string) (($snapshot['customer_name'] ?? 'Job order record').' - '.$snapshot['vehicle'])),
                            isset($log->metadata['part_category']) => trim((string) (($log->metadata['part_sku'] ?? 'Part').' - '.($log->metadata['part_category'] ?? 'Inventory'))),
                            isset($log->metadata['quantity']) => trim((string) (($log->metadata['quantity'] ?? '').' '.($log->metadata['type'] ?? 'stock'))),
                            isset($snapshot['part_category']) => trim((string) (($snapshot['part_sku'] ?? 'Part').' - '.($snapshot['part_category'] ?? 'Inventory'))),
                            isset($snapshot['quantity']) => trim((string) (($snapshot['quantity'] ?? '').' '.($snapshot['type'] ?? 'stock'))),
                            isset($snapshot['category']) => trim((string) (($snapshot['sku'] ?? 'Part').' - '.($snapshot['category'] ?? 'Inventory'))),
                            default => '',
                        };
                        $photoPath = match (true) {
                            $subject instanceof \App\Models\Customer => $subject->profile_photo_path,
                            $subject instanceof \App\Models\JobOrder => $subject->customer?->profile_photo_path ?: $subject->walk_in_profile_photo_path,
                            $subject instanceof \App\Models\Part => $subject->image_path,
                            $subject instanceof \App\Models\StockMovement => $subject->part?->image_path ?? ($log->metadata['part_image_path'] ?? null),
                            isset($snapshot['customer_profile_photo_path']) => $snapshot['customer_profile_photo_path'],
                            isset($log->metadata['part_image_path']) => $log->metadata['part_image_path'],
                            isset($snapshot['part_image_path']) => $snapshot['part_image_path'],
                            isset($snapshot['profile_photo_path']) => $snapshot['profile_photo_path'],
                            isset($snapshot['walk_in_profile_photo_path']) => $snapshot['walk_in_profile_photo_path'],
                            isset($snapshot['image_path']) => $snapshot['image_path'],
                            default => null,
                        };
                        $photoUrl = $photoPath ? \Illuminate\Support\Facades\Storage::url($photoPath) : null;
                        $recordInitials = collect(explode(' ', (string) ($recordName ?: $actionLabel)))
                            ->filter()
                            ->map(fn (string $part): string => mb_substr($part, 0, 1))
                            ->take(2)
                            ->implode('');
                        $description = preg_replace('/^(Part|Customer|Job order) (created|updated|deleted):\s*/i', '', (string) $log->description);
                        $description = trim((string) $description);
                    @endphp
                    <article class="logs-history-row logs-history-row-{{ $actionTone }}">
                        <div class="logs-history-date">
                            <span>{{ $log->created_at->timezone('Asia/Manila')->format('M d, Y') }}</span>
                            <strong>{{ $log->created_at->timezone('Asia/Manila')->format('h:i A') }}</strong>
                        </div>

                        <div class="logs-history-record {{ $recordName === '' ? 'logs-history-record-empty' : '' }}">
                            @if ($photoUrl && $recordName !== '')
                                <img src="{{ $photoUrl }}" alt="{{ $recordName }} image" class="logs-history-photo" loading="lazy" decoding="async">
                            @elseif ($recordName !== '')
                                <span class="logs-history-avatar">{{ strtoupper($recordInitials ?: $actionInitial ?: 'L') }}</span>
                            @endif
                            @if ($recordName !== '')
                                <span class="min-w-0">
                                    <strong>{{ $recordName }}</strong>
                                    @if ($recordDetail !== '')
                                        <small>{{ $recordDetail }}</small>
                                    @endif
                                </span>
                            @endif
                        </div>

                        <div>
                            <span class="logs-history-badge logs-history-badge-{{ $actionTone }}">{{ $actionLabel }}</span>
                        </div>

                        <p class="logs-history-description">
                            {{ $description !== '' ? $description : ($recordDetail !== '' ? $recordDetail : 'No additional details') }}
                        </p>

                        <div class="logs-history-actions">
                            <button
                                type="button"
                                class="logs-history-icon-button"
                                aria-label="Delete log history"
                                title="Delete log history"
                                data-open-modal="delete-log-{{ $log->id }}-modal"
                            >
                                <x-icon name="trash" class="h-4 w-4" />
                            </button>
                        </div>
                    </article>
                @empty
                    <div class="logs-history-empty">
                        <span class="logs-history-empty-icon"><x-icon name="file" class="h-6 w-6" /></span>
                        <p>No history logs recorded yet.</p>
                    </div>
                @endforelse
            </div>

            @if ($logs->hasPages())
                <div class="logs-history-pagination">
                    {{ $logs->links() }}
                </div>
            @endif
        </section>
    </section>

    @foreach ($logs as $log)
        <div class="app-modal hidden" data-modal="delete-log-{{ $log->id }}-modal">
            <div class="app-modal-card max-w-lg">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-2xl font-bold text-slate-900">Are you sure?</h3>
                        <p class="mt-2 text-sm text-slate-500">You are about to delete this log history.</p>
                    </div>
                    <button type="button" class="icon-button" data-close-modal="delete-log-{{ $log->id }}-modal" aria-label="Cancel delete log">
                        <x-icon name="x" class="h-4 w-4" />
                    </button>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="ghost-button" data-close-modal="delete-log-{{ $log->id }}-modal">Cancel</button>
                    <form method="POST" action="{{ route('logs.destroy', $log) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="danger-button">
                            <x-icon name="trash" class="h-4 w-4" />
                            <span>Yes, Delete</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <div class="app-modal hidden" data-modal="delete-all-logs-modal">
        <div class="app-modal-card max-w-lg">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-2xl font-bold text-slate-900">Are you sure?</h3>
                    <p class="mt-2 text-sm text-slate-500">You are about to delete all log history.</p>
                </div>
                <button type="button" class="icon-button" data-close-modal="delete-all-logs-modal" aria-label="Cancel delete all logs">
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" class="ghost-button" data-close-modal="delete-all-logs-modal">Cancel</button>
                <form method="POST" action="{{ route('logs.destroy-all') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="danger-button">
                        <x-icon name="trash" class="h-4 w-4" />
                        <span>Yes, Delete</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
