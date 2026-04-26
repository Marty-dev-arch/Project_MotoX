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
                                    <x-badge :tone="$tone">{{ $delta >= 0 ? '+' : '' }}{{ $delta }}</x-badge>
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
                            <th>Unit Price</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($parts as $part)
                            @php
                                $isLow = $part->current_stock < $part->minimum_stock;
                                $isOut = $part->current_stock <= 0;
                                $tone = $isOut ? 'danger' : ($isLow ? 'warning' : 'success');
                                $status = $isOut ? 'Out of Stock' : ($isLow ? 'Low Stock' : 'In Stock');
                            @endphp
                            <tr>
                                <td>
                                    @if ($part->image_path)
                                        <img src="{{ Storage::url($part->image_path) }}" alt="{{ $part->name }}" class="h-12 w-12 rounded-lg object-cover">
                                    @else
                                        <div class="h-12 w-12 rounded-lg bg-slate-100 flex items-center justify-center">
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
                                <td class="font-semibold text-slate-900">{{ $part->current_stock }}</td>
                                <td>{{ $part->minimum_stock }}</td>
                                <td class="font-semibold text-slate-900">PHP {{ number_format((float) $part->unit_price, 2) }}</td>
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
                                            data-edit-part-minimum="{{ $part->minimum_stock }}"
                                            data-edit-part-price="{{ number_format((float) $part->unit_price, 2, '.', '') }}"
                                            data-edit-part-active="{{ $part->is_active ? '1' : '0' }}"
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
                                <td colspan="9" class="py-10 text-center text-sm text-slate-500">No parts found. Add your first part to begin tracking stock.</td>
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
                    <label class="form-field md:col-span-2">
                        <span class="muted-label">Category</span>
                        <input type="text" name="category" list="part-categories" class="input-shell" required>
                        <datalist id="part-categories">
                            @foreach ($partCategories as $category)
                                <option value="{{ $category }}"></option>
                            @endforeach
                        </datalist>
                    </label>
                    <label class="form-field">
                        <span class="muted-label">Minimum Stock</span>
                        <input type="number" name="minimum_stock" min="0" class="input-shell" value="0" required>
                    </label>
                </div>

                <div class="space-y-4">
                    <label class="form-field">
                        <span class="muted-label">Part Image</span>
                        <input type="file" name="image" accept="image/*" class="input-shell">
                    </label>
                </div>
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
                    <label class="form-field md:col-span-2">
                        <span class="muted-label">Category</span>
                        <input type="text" name="category" class="input-shell" data-edit-field="category" required>
                    </label>
                    <label class="form-field">
                        <span class="muted-label">Minimum Stock</span>
                        <input type="number" name="minimum_stock" min="0" class="input-shell" data-edit-field="minimum" required>
                    </label>
                </div>

                <div class="space-y-4">
                    <label class="form-field">
                        <span class="muted-label">Part Image</span>
                        <input type="file" name="image" accept="image/*" class="input-shell">
                    </label>
                </div>
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
                        <input type="number" name="quantity" class="input-shell" value="1" required>
                    </label>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="form-field">
                        <span class="muted-label">Reason</span>
                        <input type="text" name="reason" class="input-shell" placeholder="e.g. Supplier restock">
                    </label>
                    <label class="form-field">
                        <span class="muted-label">Reference</span>
                        <input type="text" name="reference" class="input-shell" placeholder="PO-2109 / JO-112">
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" class="ghost-button" data-close-modal="movement-modal">Cancel</button>
                    <button type="submit" class="primary-button">Save Movement</button>
                </div>
            </form>
        </div>
    </div>
@endsection
