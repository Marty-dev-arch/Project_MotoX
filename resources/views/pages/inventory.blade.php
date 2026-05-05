@extends('layouts.app')

@section('content')
    <section class="space-y-6">
        @if (session('status'))
            <div class="auth-alert">
                <p class="font-semibold">{{ session('status') }}</p>
            </div>
        @endif

        @if ($errors->has('inventory'))
            <div class="auth-alert">
                <p class="font-semibold">{{ $errors->first('inventory') }}</p>
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
            <section class="panel-card p-5 sm:p-6">
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

            <section class="panel-card p-5 sm:p-6">
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
                        @endphp
                        <article class="rounded-2xl border border-slate-100 bg-white px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $movement->part?->name ?? 'Part Removed' }}</p>
                                    <p class="text-sm text-slate-500">{{ ucfirst($movement->type) }} &middot; {{ $movement->reason ?: 'Stock update' }}</p>
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
            <section class="panel-card p-5 sm:p-6">
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
                            $current = rtrim(rtrim(number_format((float) $part->current_stock, 3, '.', ''), '0'), '.');
                            $minimum = rtrim(rtrim(number_format((float) $part->minimum_stock, 3, '.', ''), '0'), '.');
                        @endphp
                        <article class="detail-card">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-base font-bold text-slate-900">{{ $part->name }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $part->sku }} &middot; {{ $part->category }}</p>
                                </div>
                                <x-badge :tone="$isOut ? 'danger' : 'warning'">{{ $isOut ? 'Sold out' : 'Low' }}</x-badge>
                            </div>
                            <p class="mt-4 text-sm font-semibold text-slate-700">{{ $current }} / {{ $minimum }} {{ $part->unit_label }}</p>
                            <button
                                type="button"
                                class="ghost-button mt-4 w-full justify-center"
                                data-open-modal="movement-modal"
                                data-movement-part-id="{{ $part->id }}"
                                data-movement-part-name="{{ $part->name }}"
                                data-movement-part-stock="{{ $part->current_stock }}"
                                data-movement-part-mode="{{ $part->stock_mode }}"
                                data-movement-part-unit="{{ $part->unit_label }}"
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
                <table class="soft-table">
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
                                $stockDisplay = rtrim(rtrim(number_format((float) $part->current_stock, 3, '.', ''), '0'), '.');
                                $conversion = (float) ($part->pieces_per_box ?? 0);
                                $stockBreakdown = null;
                                if ($part->stock_mode === 'box_piece' && $conversion > 0) {
                                    $wholeBoxes = floor((float) $part->current_stock / $conversion);
                                    $loosePieces = round((float) $part->current_stock - ($wholeBoxes * $conversion), 3);
                                    $stockBreakdown = "{$wholeBoxes} box".($wholeBoxes == 1 ? '' : 'es')." + ".rtrim(rtrim(number_format($loosePieces, 3, '.', ''), '0'), '.').' '.$part->unit_label;
                                }
                            @endphp
                            <tr>
                                <td>
                                    @if ($part->image_path)
                                        <img src="{{ Storage::url($part->image_path) }}" alt="{{ $part->name }}" class="h-12 w-12 rounded-full object-cover">
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
                                    {{ $stockDisplay }} {{ $part->unit_label }}
                                    @if ($stockBreakdown)
                                        <span class="block text-xs font-medium text-slate-500">{{ $stockBreakdown }}</span>
                                    @endif
                                </td>
                                <td>{{ rtrim(rtrim(number_format((float) $part->minimum_stock, 3, '.', ''), '0'), '.') }}</td>
                                <td>{{ $part->unit_label }}</td>
                                <td class="font-semibold text-slate-900">
                                    PHP {{ number_format((float) $part->unit_price, 2) }}
                                    @if ($part->stock_mode === 'box_piece')
                                        <span class="block text-xs text-slate-500">per box</span>
                                    @elseif ($part->stock_mode === 'liquid')
                                        <span class="block text-xs text-slate-500">per {{ $part->unit_label }}</span>
                                    @else
                                        <span class="block text-xs text-slate-500">per {{ $part->unit_label }}</span>
                                    @endif
                                </td>
                                <td><x-badge :tone="$tone">{{ $status }}</x-badge></td>
                                <td>
                                    <div class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            class="icon-button"
                                            aria-label="Add movement"
                                            data-open-modal="movement-modal"
                                            data-movement-part-id="{{ $part->id }}"
                                            data-movement-part-name="{{ $part->name }}"
                                            data-movement-part-stock="{{ $part->current_stock }}"
                                            data-movement-part-mode="{{ $part->stock_mode }}"
                                            data-movement-part-unit="{{ $part->unit_label }}"
                                        >
                                            <x-icon name="trend" class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            class="icon-button"
                                            aria-label="Edit part"
                                            data-open-modal="edit-part-modal"
                                            data-edit-part-id="{{ $part->id }}"
                                            data-edit-part-name="{{ $part->name }}"
                                            data-edit-part-sku="{{ $part->sku }}"
                                            data-edit-part-category="{{ $part->category }}"
                                            data-edit-part-mode="{{ $part->stock_mode }}"
                                            data-edit-part-unit="{{ $part->unit_label }}"
                                            data-edit-part-box-size="{{ rtrim(rtrim(number_format((float) ($part->pieces_per_box ?? 0), 3, '.', ''), '0'), '.') }}"
                                            data-edit-part-allow-fractional="{{ $part->allow_fractional_quantity ? '1' : '0' }}"
                                            data-edit-part-minimum="{{ $part->minimum_stock }}"
                                            data-edit-part-price="{{ number_format((float) $part->unit_price, 2, '.', '') }}"
                                            data-edit-part-active="{{ $part->is_active ? '1' : '0' }}"
                                            data-edit-part-image-url="{{ $part->image_path ? Storage::url($part->image_path) : '' }}"
                                        >
                                            <x-icon name="pencil" class="h-4 w-4" />
                                        </button>
                                        <form method="POST" action="{{ route('inventory.parts.destroy', $part) }}" onsubmit="return confirm('Delete this part?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="icon-button" aria-label="Delete part">
                                                <x-icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </form>
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

    <div class="app-modal hidden" data-modal="create-part-modal">
        <div class="app-modal-card">
            <div class="flex items-center justify-between gap-3">
                <h3 class="text-2xl font-bold text-slate-900">Add New Part</h3>
                <button type="button" class="icon-button" data-close-modal="create-part-modal">
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>

            <form method="POST" action="{{ route('inventory.parts.store') }}" class="mt-6 space-y-4" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="form_context" value="create">

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="form-field">
                        <span class="muted-label">Part Name</span>
                        <input type="text" name="name" class="input-shell" required>
                    </label>
                    <label class="form-field">
                        <span class="muted-label">SKU</span>
                        <input type="text" name="sku" class="input-shell" required>
                    </label>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <label class="form-field">
                        <span class="muted-label">Category</span>
                        <input type="text" name="category" list="part-categories" class="input-shell" required>
                        <datalist id="part-categories">
                            @foreach ($partCategories as $category)
                                <option value="{{ $category }}"></option>
                            @endforeach
                        </datalist>
                    </label>
                    <label class="form-field">
                        <span class="muted-label">Stock Mode</span>
                        <select name="stock_mode" class="input-shell" required>
                            <option value="piece" selected>Piece</option>
                            <option value="box_piece">Box</option>
                            <option value="liquid">Liquid</option>
                        </select>
                    </label>
                    <label class="form-field">
                        <span class="muted-label">Unit Label</span>
                        <input type="text" name="unit_label" class="input-shell" value="pcs" required>
                    </label>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <label class="form-field">
                        <span class="muted-label">Pieces per Box</span>
                        <input type="number" name="pieces_per_box" min="0.001" step="0.001" class="input-shell" placeholder="10 pieces">
                    </label>
                    <label class="form-field">
                        <span class="muted-label">Allow Fractional Quantity</span>
                        <select name="allow_fractional_quantity" class="input-shell">
                            <option value="0" selected>No</option>
                            <option value="1">Yes</option>
                        </select>
                    </label>
                    <label class="form-field">
                        <span class="muted-label">Minimum Stock</span>
                        <input type="number" name="minimum_stock" min="0" step="0.001" class="input-shell" value="0" required>
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
                        <span class="part-upload-title">Choose your spare parts file</span>
                        <span class="part-upload-note">PNG, JPG, WEBP up to 2MB.</span>
                    </span>
                    <input type="file" name="image" accept="image/*" class="sr-only" data-image-preview-input="create-part-image">
                </label>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="form-field">
                        <span class="muted-label">Unit Price (PHP)</span>
                        <input type="number" name="unit_price" min="0" step="0.01" class="input-shell" value="0.00" required>
                    </label>
                    <label class="form-field">
                        <span class="muted-label">Status</span>
                        <select name="is_active" class="input-shell">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" class="ghost-button" data-close-modal="create-part-modal">Cancel</button>
                    <button type="submit" class="primary-button">Save Part</button>
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
                data-action-template="{{ route('inventory.parts.update', ['part' => '__PART_ID__']) }}"
            >
                @csrf
                @method('PUT')
                <input type="hidden" name="form_context" value="edit">

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

                <div class="grid gap-4 md:grid-cols-3">
                    <label class="form-field">
                        <span class="muted-label">Category</span>
                        <input type="text" name="category" class="input-shell" data-edit-field="category" required>
                    </label>
                    <label class="form-field">
                        <span class="muted-label">Stock Mode</span>
                        <select name="stock_mode" class="input-shell" data-edit-field="mode" required>
                            <option value="piece">Piece</option>
                            <option value="box_piece">Box</option>
                            <option value="liquid">Liquid</option>
                        </select>
                    </label>
                    <label class="form-field">
                        <span class="muted-label">Unit Label</span>
                        <input type="text" name="unit_label" class="input-shell" data-edit-field="unit" required>
                    </label>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <label class="form-field">
                        <span class="muted-label">Pieces per Box</span>
                        <input type="number" name="pieces_per_box" min="0.001" step="0.001" class="input-shell" data-edit-field="box_size" placeholder="10 pieces">
                    </label>
                    <label class="form-field">
                        <span class="muted-label">Allow Fractional Quantity</span>
                        <select name="allow_fractional_quantity" class="input-shell" data-edit-field="allow_fractional">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </label>
                    <label class="form-field">
                        <span class="muted-label">Minimum Stock</span>
                        <input type="number" name="minimum_stock" min="0" step="0.001" class="input-shell" data-edit-field="minimum" required>
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
                        <span class="muted-label">Unit Price (PHP)</span>
                        <input type="number" name="unit_price" min="0" step="0.01" class="input-shell" data-edit-field="price" required>
                    </label>
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
                    <button type="submit" class="primary-button">Update Part</button>
                </div>
            </form>
        </div>
    </div>

    <div class="app-modal hidden" data-modal="movement-modal">
        <div class="app-modal-card">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-2xl font-bold text-slate-900">Record Stock Movement</h3>
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
                data-action-template="{{ route('inventory.parts.movements.store', ['part' => '__PART_ID__']) }}"
            >
                @csrf
                <input type="hidden" name="form_context" value="movement">

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="form-field">
                        <span class="muted-label">Movement Type</span>
                        <select name="type" class="input-shell" required>
                            <option value="in">Stock In (+)</option>
                            <option value="out">Stock Out (-)</option>
                            <option value="adjust">Adjustment (+/-)</option>
                        </select>
                    </label>
                    <label class="form-field">
                        <span class="muted-label">Quantity</span>
                        <input type="number" name="quantity" class="input-shell" value="1" step="0.001" required>
                    </label>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="form-field">
                        <span class="muted-label">Quantity Unit</span>
                        <select name="quantity_unit" class="input-shell" data-movement-unit-select>
                            <option value="piece">Piece</option>
                            <option value="box">Box</option>
                            <option value="liter">Liter</option>
                        </select>
                    </label>
                </div>

                <label class="form-field">
                    <span class="muted-label">Reason</span>
                    <input type="text" name="reason" class="input-shell" placeholder="e.g. Supplier restock">
                </label>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" class="ghost-button" data-close-modal="movement-modal">Cancel</button>
                    <button type="submit" class="primary-button">Save Movement</button>
                </div>
            </form>
        </div>
    </div>
@endsection
