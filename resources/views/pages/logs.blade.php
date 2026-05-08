@extends('layouts.app')

@section('content')
    <section class="space-y-6">
        @if (session('status'))
            <div class="auth-alert auth-alert-{{ session('status_tone', 'success') }}">
                <p class="font-semibold">{{ session('status') }}</p>
            </div>
        @endif

        <div class="flex flex-wrap items-start justify-between gap-5">
            <div>
                <h1 class="text-4xl font-black tracking-tight text-slate-900">{{ $heading }}</h1>
                <p class="mt-2 text-sm text-slate-500">{{ $subheading }}</p>
            </div>

            <button
                type="button"
                class="ghost-button border-brand-200 text-brand-600 hover:border-brand-300 hover:bg-brand-50"
                data-open-modal="delete-all-logs-modal"
                @disabled($logs->total() === 0)
            >
                <x-icon name="trash" class="h-4 w-4" />
                <span>Delete All History</span>
            </button>
        </div>

        <section class="table-shell">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">All History</h2>
                    <p class="text-sm text-slate-500">{{ number_format($logs->total()) }} history {{ $logs->total() === 1 ? 'entry' : 'entries' }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="soft-table w-full min-w-[980px]">
                    <thead>
                        <tr class="table-heading">
                            <th>DATE & Time</th>
                            <th>User</th>
                            <th>Trigger</th>
                            <th>Description</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>
                                    <p class="font-semibold text-slate-900">{{ $log->created_at->timezone('Asia/Manila')->format('M d, Y') }}</p>
                                    <p class="text-xs text-slate-500">{{ $log->created_at->timezone('Asia/Manila')->format('h:i A') }}</p>
                                </td>
                                <td>
                                    <p class="font-semibold text-slate-900">{{ $log->user?->name ?? 'System' }}</p>
                                    <p class="text-xs text-slate-500">{{ $log->user?->email ?? 'No user account' }}</p>
                                </td>
                                <td>
                                    @php
                                        $actionTone = str_contains($log->action, 'deleted') || str_contains($log->action, 'out')
                                            ? 'danger'
                                            : (str_contains($log->action, 'updated') ? 'warning' : 'success');
                                    @endphp
                                    <x-badge :tone="$actionTone">{{ str_replace(['.', '_'], ' ', ucfirst($log->action)) }}</x-badge>
                                </td>
                                <td class="max-w-[420px]">
                                    <p class="text-sm text-slate-700">{{ $log->description }}</p>
                                </td>
                                <td>
                                    <div class="flex justify-end">
                                        <button
                                            type="button"
                                            class="icon-button"
                                            aria-label="Delete log history"
                                            data-open-modal="delete-log-{{ $log->id }}-modal"
                                        >
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-sm text-slate-500">No history logs recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">
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
                        <button type="submit" class="danger-button">Yes, Delete</button>
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
                    <button type="submit" class="danger-button">Yes, Delete</button>
                </form>
            </div>
        </div>
    </div>
@endsection
