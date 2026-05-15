@extends('layouts.app')

@section('content')
    @php
        $formatStockQuantity = function (float $stock, float $piecesPerBox): array {
            $piecesPerBox = max(1.0, $piecesPerBox);
            $wholeBoxes = (int) floor($stock / $piecesPerBox);
            $loosePieces = round($stock - ($wholeBoxes * $piecesPerBox), 3);
            $formatNumber = fn (float $value): string => rtrim(rtrim(number_format($value, 3, '.', ''), '0'), '.');
            $parts = [];

            if ($wholeBoxes > 0) {
                $parts[] = $wholeBoxes.' box'.($wholeBoxes === 1 ? '' : 'es');
            }

            if ($loosePieces > 0 || $parts === []) {
                $parts[] = $formatNumber(max(0, $loosePieces)).' piece'.(abs($loosePieces - 1.0) < 0.00001 ? '' : 's');
            }

            return [
                'display' => implode(' + ', $parts),
                'total' => $formatNumber(max(0, $stock)).' total piece'.(abs($stock - 1.0) < 0.00001 ? '' : 's'),
            ];
        };
    @endphp

    <section class="space-y-6">
        @if (session('status'))
            <div class="auth-alert auth-alert-{{ session('status_tone', 'success') }}">
                <p class="font-semibold">{{ session('status') }}</p>
            </div>
        @endif

        @if ($errors->has('inventory'))
            <div class="auth-alert auth-alert-danger">
                <p class="font-semibold">{{ $errors->first('inventory') }}</p>
            </div>
        @endif

        @if ($errors->any() && ! $errors->has('inventory'))
            <div class="auth-alert auth-alert-danger">
                <p class="font-semibold">{{ $errors->first() }}</p>
            </div>
        @endif

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-4xl font-black tracking-tight text-slate-900">{{ $heading }}</h1>
                <p class="mt-2 text-sm text-slate-500">{{ $meta }}</p>
            </div>
            <button type="button" class="primary-button" data-open-modal="create-part-modal">
                <x-icon name="plus" class="h-4 w-4" />
                <span>Add Part</span>
            </button>
        </div>

        <div class="grid gap-4 md:grid-cols-2 2xl:grid-cols-4">
            @foreach ($stats as $stat)
                <x-kpi-card
                    :label="$stat['label']"
                    :value="$stat['value']"
                    :caption="$stat['caption']"
                    :icon="$stat['icon']"
                    :tone="$stat['tone'] ?? 'default'"
                />
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-[1fr_1fr]">
            <section class="panel-card p-5 sm:p-6 inventory-compact-panel">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">Category Snapshot</h2>
                        <p class="mt-1 text-sm text-slate-500">Track volume and low-stock concentration per category.</p>
                    </div>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    @foreach ($categories as $category)
                        <article class="rounded-2xl border border-slate-100 bg-slate-50/80 px-4 py-4">
                            <p class="text-lg font-semibold text-slate-900">{{ $category['name'] }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $category['count'] }} part{{ $category['count'] === 1 ? '' : 's' }}</p>
                            <p class="mt-2 text-xs font-semibold uppercase tracking-[0.14em] {{ $category['low'] > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                                {{ $category['low'] > 0 ? "{$category['low']} low stock" : 'Healthy stock' }}
                            </p>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="panel-card p-5 sm:p-6 inventory-compact-panel">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">Recent Stock Movements</h2>
                        <p class="mt-1 text-sm text-slate-500">Ledger entries powering real-time stock levels.</p>
                    </div>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($movements->take(6) as $movement)
                        @php
                            $delta = $movement->delta();
                            $tone = $delta > 0 ? 'success' : ($delta < 0 ? 'danger' : 'accent');
                            $movementPartImageUrl = $movement->part?->image_path && Storage::disk('public')->exists($movement->part->image_path)
                                ? Storage::url($movement->part->image_path)
                                : null;
                        @endphp
                        <article class="rounded-2xl border border-slate-100 bg-white px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    @if ($movementPartImageUrl)
                                        <img src="{{ $movementPartImageUrl }}" alt="{{ $movement->part?->name ?? 'Part' }}" class="stock-movement-thumb" loading="lazy" decoding="async">
                                    @else
                                        <span class="stock-movement-thumb stock-movement-thumb-empty">
                                            <x-icon name="image" class="h-5 w-5" />
                                        </span>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-slate-900">{{ $movement->part?->name ?? 'Part Removed' }}</p>
                                        <p class="truncate text-sm text-slate-500">{{ ucfirst($movement->type) }} &middot; {{ $movement->reason ?: 'Stock update' }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <x-badge :tone="$tone">
                                        {{ $delta >= 0 ? '+' : '' }}{{ rtrim(rtrim(number_format((float) $delta, 3, '.', ''), '0'), '.') }}
                                    </x-badge>
                                    <p class="mt-1 text-xs text-slate-400">{{ \App\Support\InventoryMetrics::formatMovementTime($movement->moved_at) }}</p>
                                </div>
                            </div>
                        </article>
                    @empty
                        <p class="text-sm text-slate-500">No movement history yet.</p>
                    @endforelse
                </div>
            </section>
        </div>

        @if ($alerts->isNotEmpty())
            <section class="panel-card p-5 sm:p-6 inventory-compact-panel">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">Restock Queue</h2>
                        <p class="mt-1 text-sm text-slate-500">Sold-out and below-minimum parts that need replenishment.</p>
                    </div>
                </div>

                <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    @foreach ($alerts as $part)
                        @php
                            $isOut = (float) $part->current_stock <= 0;
                            $conversion = max(1.0, (float) ($part->pieces_per_box ?? 1));
                            $stockQuantity = $formatStockQuantity((float) $part->current_stock, $conversion);
                            $current = $stockQuantity['display'];
                            $minimum = rtrim(rtrim(number_format((float) $part->minimum_stock, 3, '.', ''), '0'), '.');
                            $displayUnitLabel = $part->unit_label ?: 'box';
                            $editStockMode = 'box_piece';
                        @endphp
                        <article class="detail-card inventory-restock-card">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-base font-bold text-slate-900">{{ $part->name }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $part->sku }} &middot; {{ $part->category }}</p>
                                </div>
                                <x-badge :tone="$isOut ? 'danger' : 'warning'">{{ $isOut ? 'Sold out' : 'Low' }}</x-badge>
                            </div>
                            <p class="mt-4 text-sm font-semibold text-slate-700">{{ $current }}</p>
                            <button
                                type="button"
                                class="ghost-button mt-4 w-full justify-center"
                                data-open-modal="movement-modal"
                                data-movement-part-id="{{ $part->id }}"
                                data-movement-part-name="{{ $part->name }}"
                                data-movement-part-stock="{{ $current }}"
                                data-movement-part-mode="{{ $editStockMode }}"
                                data-movement-part-unit="{{ $displayUnitLabel }}"
                            >
                                <x-icon name="plus" class="h-4 w-4" />
                                <span>Restock</span>
                            </button>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="table-shell">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h3 class="text-2xl font-bold tracking-tight text-slate-900">Spare Parts Ledger</h3>
                    <p class="text-sm text-slate-500">Create, edit, move stock, and monitor minimum levels in one place.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="soft-table w-full min-w-[1180px]">
                    <thead>
<tr class="table-heading">
                            <th width="80">Image</th>
                            <th>Part</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th>Current</th>
                            <th>Minimum</th>
                            <th>Unit</th>
                            <th>Unit Price</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($parts as $part)
                            @php
                                $isLow = $part->current_stock > 0 && $part->current_stock < $part->minimum_stock;
                                $isOut = $part->current_stock <= 0;
                                $tone = $isOut ? 'danger' : ($isLow ? 'warning' : 'success');
                                $status = $isOut ? 'Out of Stock' : ($isLow ? 'Low Stock' : 'In Stock');
                                $displayUnitLabel = $part->unit_label ?: 'box';
                                $editStockMode = 'box_piece';
                                $conversion = max(1.0, (float) ($part->pieces_per_box ?? 1));
                                $stockBreakdown = null;
                                $partImageUrl = $part->image_path && Storage::disk('public')->exists($part->image_path)
                                    ? Storage::url($part->image_path)
                                    : null;
                                $stockQuantity = $formatStockQuantity((float) $part->current_stock, $conversion);
                                $stockDisplay = $stockQuantity['display'];
                                $stockBreakdown = $stockQuantity['total'];
                            @endphp
                            <tr>
                                <td>
                                    @if ($partImageUrl)
                                        <img src="{{ $partImageUrl }}" alt="{{ $part->name }}" class="h-12 w-12 rounded-full object-cover" loading="lazy" decoding="async">
                                    @else
                                        <div class="h-12 w-12 rounded-full bg-slate-100 flex items-center justify-center">
                                            <x-icon name="image" class="h-6 w-6 text-slate-400" />
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="space-y-1">
                                        <p class="font-semibold text-slate-900">{{ $part->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $part->is_active ? 'Active' : 'Inactive' }}</p>
                                    </div>
                                </td>
                                <td>{{ $part->sku }}</td>
                                <td>{{ $part->category }}</td>
                                <td class="font-semibold text-slate-900">
                                    {{ $stockDisplay }}
                                    @if ($stockBreakdown)
                                        <span class="block text-xs font-medium text-slate-500">{{ $stockBreakdown }}</span>
                                    @endif
                                </td>
                                <td>{{ rtrim(rtrim(number_format((float) $part->minimum_stock, 3, '.', ''), '0'), '.') }}</td>
                                <td>{{ ucfirst($displayUnitLabel) }}</td>
                                <td class="font-semibold text-slate-900">
                                    PHP {{ number_format((float) ($part->unit_price_per_box ?? $part->unit_price), 2) }}
                                    <span class="block text-xs text-slate-500">per box</span>
                                    <span class="block text-xs text-slate-500">PHP {{ number_format((float) ($part->unit_price_per_piece ?? 0), 2) }} per pieces</span>
                                </td>
                                <td><x-badge :tone="$tone">{{ $status }}</x-badge></td>
                                <td>
                                    <div class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            class="icon-button"
                                            aria-label="Add movement"
                                            title="Add movement"
                                            data-open-modal="movement-modal"
                                            data-movement-part-id="{{ $part->id }}"
                                            data-movement-part-name="{{ $part->name }}"
                                            data-movement-part-stock="{{ $stockDisplay }}"
                                            data-movement-part-mode="{{ $editStockMode }}"
                                            data-movement-part-unit="{{ $displayUnitLabel }}"
                                        >
                                            <x-icon name="trend" class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            class="icon-button"
                                            aria-label="Edit inventory"
                                            title="Edit inventory"
                                            data-open-modal="edit-part-modal"
                                            data-edit-part-id="{{ $part->id }}"
                                            data-edit-part-name="{{ $part->name }}"
                                            data-edit-part-sku="{{ $part->sku }}"
                                            data-edit-part-category="{{ $part->category }}"
                                            data-edit-part-mode="{{ $editStockMode }}"
                                            data-edit-part-unit="{{ $displayUnitLabel }}"
                                            data-edit-part-container-quantity="{{ rtrim(rtrim(number_format((float) ($part->pieces_per_box ?? 1), 3, '.', ''), '0'), '.') }}"
                                            data-edit-part-minimum="{{ $part->minimum_stock }}"
                                            data-edit-part-price="{{ number_format((float) $part->unit_price, 2, '.', '') }}"
                                            data-edit-part-price-per-box="{{ number_format((float) ($part->unit_price_per_box ?? $part->unit_price), 2, '.', '') }}"
                                            data-edit-part-price-per-piece="{{ number_format((float) ($part->unit_price_per_piece ?? 0), 2, '.', '') }}"
                                            data-edit-part-active="{{ $part->is_active ? '1' : '0' }}"
                                            data-edit-part-image-url="{{ $partImageUrl ?? '' }}"
                                        >
                                            <x-icon name="pencil" class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            class="icon-button"
                                            aria-label="Delete part"
                                            title="Delete part"
                                            data-open-modal="delete-part-{{ $part->id }}-modal"
                                        >
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-10 text-center text-sm text-slate-500">No parts found. Add your first part to begin tracking stock.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </section>

    @foreach ($parts as $part)
        <div class="app-modal hidden" data-modal="delete-part-{{ $part->id }}-modal">
            <div class="app-modal-card max-w-lg">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-2xl font-bold text-slate-900">Are you sure?</h3>
                        <p class="mt-2 text-sm text-slate-500">You are about to delete this Part.</p>
                    </div>
                    <button type="button" class="icon-button" data-close-modal="delete-part-{{ $part->id }}-modal" aria-label="Cancel delete part">
                        <x-icon name="x" class="h-4 w-4" />
                    </button>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="ghost-button" data-close-modal="delete-part-{{ $part->id }}-modal">Cancel</button>
                    <form method="POST" action="{{ route('inventory.parts.destroy', $part) }}">
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

    <div class="app-modal hidden" data-modal="create-part-modal">
        <div class="app-modal-card">
            <div class="flex items-center justify-between gap-3">
                <h3 class="text-2xl font-bold text-slate-900">Add New Part</h3>
                <button type="button" class="icon-button" data-close-modal="create-part-modal">
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>

            <form method="POST" action="{{ route('inventory.parts.store') }}" class="mt-6 space-y-4" enctype="multipart/form-data" data-inventory-action-form>
                @csrf
                <input type="hidden" name="form_context" value="create">
                <input type="hidden" name="stock_mode" value="box_piece">
                <input type="hidden" name="is_active" value="1">
                <input type="hidden" name="unit_price_basis" value="per_box">

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="form-field">
                        <span class="muted-label">Part Name</span>
                        <input type="text" name="name" class="input-shell" required>
                    </label>
                    <label class="form-field">
                        <span class="muted-label">Part Number / SKU</span>
                        <input type="text" name="sku" class="input-shell" required>
                    </label>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="form-field">
                        <span class="muted-label">Category</span>
                        <select name="category" class="input-shell" required>
                            @foreach ($partCategories as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-field">
                        <span class="muted-label">Unit Label</span>
                        <input type="text" name="unit_label" class="input-shell" value="box" placeholder="box" required>
                    </label>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="form-field">
                        <span class="muted-label" data-container-quantity-label>Pieces per Box</span>
                        <input type="number" name="container_quantity" min="1" step="1" class="input-shell" value="1" required>
                        <span class="text-xs text-slate-500" data-container-quantity-help></span>
                    </label>
                    <label class="form-field">
                        <span class="muted-label">Minimum Stock</span>
                        <input type="number" name="minimum_stock" min="0" step="1" class="input-shell" value="0" required>
                    </label>
                </div>

                <label class="part-upload-card">
                    <span class="part-upload-preview">
                        <img src="" alt="Selected part image preview" class="hidden" data-image-preview="create-part-image">
                        <span data-image-preview-placeholder="create-part-image">
                            <x-icon name="image" class="h-8 w-8" />
                        </span>
                    </span>
                    <span class="part-upload-content">
                        <span class="part-upload-title">Choose spare part image</span>
                        <span class="part-upload-note">PNG, JPG, WEBP up to 2MB.</span>
                    </span>
                    <input type="file" name="image" accept="image/*" class="sr-only" data-image-preview-input="create-part-image">
                </label>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="form-field">
                        <span class="muted-label">Price per Box (PHP)</span>
                        <input type="number" name="unit_price_per_box" min="0" step="0.01" class="input-shell" value="0.00" required>
                    </label>
                    <label class="form-field">
                        <span class="muted-label">Price per Piece (PHP)</span>
                        <input type="number" name="unit_price_per_piece" min="0" step="0.01" class="input-shell" value="0.00" required>
                    </label>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="form-field">
                        <span class="muted-label">Stock (boxes)</span>
                        <input type="number" name="initial_stock" min="0" step="1" class="input-shell" value="0" required>
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" class="ghost-button" data-close-modal="create-part-modal">Cancel</button>
                    <button type="submit" class="primary-button">
                        <x-icon name="check-circle" class="h-4 w-4" />
                        <span>Save Part</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="app-modal hidden" data-modal="edit-part-modal">
        <div class="app-modal-card">
            <div class="flex items-center justify-between gap-3">
                <h3 class="text-2xl font-bold text-slate-900">Edit Part</h3>
                <button type="button" class="icon-button" data-close-modal="edit-part-modal">
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>

            <form
                method="POST"
                action="#"
                class="mt-6 space-y-4"
                enctype="multipart/form-data"
                data-edit-form
                data-inventory-action-form
                data-action-template="{{ route('inventory.parts.update', ['part' => '__PART_ID__']) }}"
            >
                @csrf
                @method('PUT')
                <input type="hidden" name="form_context" value="edit">
                <input type="hidden" name="stock_mode" value="box_piece">
                <input type="hidden" name="unit_price_basis" value="per_box">

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="form-field">
                        <span class="muted-label">Part Name</span>
                        <input type="text" name="name" class="input-shell" data-edit-field="name" required>
                    </label>
                    <label class="form-field">
                        <span class="muted-label">SKU</span>
                        <input type="text" name="sku" class="input-shell" data-edit-field="sku" required>
                    </label>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="form-field">
                        <span class="muted-label">Category</span>
                        <select name="category" class="input-shell" data-edit-field="category" required>
                            @foreach ($partCategories as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-field">
                        <span class="muted-label">Unit Label</span>
                        <input type="text" name="unit_label" class="input-shell" data-edit-field="unit" required>
                    </label>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="form-field">
                        <span class="muted-label" data-container-quantity-label>Pieces per Box</span>
                        <input type="number" name="container_quantity" min="1" step="1" class="input-shell" data-edit-field="container_quantity" placeholder="10 pieces" required>
                        <span class="text-xs text-slate-500" data-container-quantity-help></span>
                    </label>
                    <label class="form-field">
                        <span class="muted-label">Minimum Stock</span>
                        <input type="number" name="minimum_stock" min="0" step="1" class="input-shell" data-edit-field="minimum" required>
                    </label>
                </div>

                <label class="part-upload-card">
                    <span class="part-upload-preview">
                        <img src="" alt="Selected part image preview" class="hidden" data-image-preview="edit-part-image">
                        <span data-image-preview-placeholder="edit-part-image">
                            <x-icon name="image" class="h-8 w-8" />
                        </span>
                    </span>
                    <span class="part-upload-content">
                        <span class="part-upload-title">Choose your spare parts file</span>
                        <span class="part-upload-note">PNG, JPG, WEBP up to 2MB.</span>
                    </span>
                    <input type="file" name="image" accept="image/*" class="sr-only" data-image-preview-input="edit-part-image">
                </label>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="form-field">
                        <span class="muted-label">Price per Box (PHP)</span>
                        <input type="number" name="unit_price_per_box" min="0" step="0.01" class="input-shell" data-edit-field="price_per_box" required>
                    </label>
                    <label class="form-field">
                        <span class="muted-label">Price per Piece (PHP)</span>
                        <input type="number" name="unit_price_per_piece" min="0" step="0.01" class="input-shell" data-edit-field="price_per_piece" required>
                    </label>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="form-field">
                        <span class="muted-label">Status</span>
                        <select name="is_active" class="input-shell" data-edit-field="active">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" class="ghost-button" data-close-modal="edit-part-modal">Cancel</button>
                    <button type="submit" class="primary-button">
                        <x-icon name="pencil" class="h-4 w-4" />
                        <span>Update Part</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="app-modal hidden" data-modal="movement-modal">
        <div class="app-modal-card">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-2xl font-bold text-slate-900">Stock Movement</h3>
                    <p class="mt-1 text-sm text-slate-500" data-movement-label></p>
                </div>
                <button type="button" class="icon-button" data-close-modal="movement-modal">
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>

            <form
                method="POST"
                action="#"
                class="mt-6 space-y-4"
                data-movement-form
                data-inventory-action-form
                data-action-template="{{ route('inventory.parts.movements.store', ['part' => '__PART_ID__']) }}"
            >
                @csrf
                <input type="hidden" name="form_context" value="movement">

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="form-field">
                        <span class="muted-label">Movement Type</span>
                        <select name="type" class="input-shell" data-movement-type-select required>
                            <option value="in">Stock In (+)</option>
                            <option value="out">Stock Out (-)</option>
                        </select>
                    </label>
                    <label class="form-field">
                        <span class="muted-label">Quantity</span>
                        <input type="number" name="quantity" class="input-shell" value="1" step="1" required>
                    </label>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="form-field">
                        <span class="muted-label">Quantity Unit</span>
                        <select name="quantity_unit" class="input-shell" data-movement-unit-select>
                            <option value="box">Box</option>
                        </select>
                    </label>
                </div>

                <label class="form-field">
                    <span class="muted-label">Reason</span>
                    <input type="text" name="reason" class="input-shell" placeholder="e.g. Supplier restock">
                </label>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" class="ghost-button" data-close-modal="movement-modal">Cancel</button>
                    <button type="submit" class="primary-button">
                        <x-icon name="check-circle" class="h-4 w-4" />
                        <span>Save Movement</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
