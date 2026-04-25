<aside class="hidden border-r border-slate-200 bg-white px-5 py-6 transition-all duration-300 xl:flex xl:min-h-screen xl:flex-col xl:border-r-white/60" id="sidebar">
        <button
            id="sidebar-inline-toggle"
            type="button"
            class="sidebar-inline-toggle xl:hidden absolute top-4 right-4 z-50"
            aria-label="Close sidebar"
            title="Close sidebar"
        >
            <x-icon name="menu" class="h-5 w-5" />
        </button>
        <div class="space-y-6">
            <div>
                <a href="{{ route('landing') }}" class="inline-flex items-center gap-2 text-2xl font-black tracking-tight text-slate-900 transition hover:text-brand-700">
                    <x-icon name="car" class="h-5 w-5 text-brand-700" />
                    <span>MotoX</span>
                </a>
                <p class="mt-1 text-sm text-slate-500">Mechanic Workspace</p>
            </div>

            <a href="{{ route('job-orders', ['create' => 1]) }}" class="primary-button w-full justify-center">
                <x-icon name="plus" class="h-4 w-4" />
                <span>New Job Order</span>
            </a>

        <nav class="space-y-1">
            @foreach ($navigation as $item)
                @php
                    $active = request()->routeIs($item['route']);
                @endphp
                <a
                    href="{{ route($item['route']) }}"
                    @class([
                        'sidebar-link',
                        'sidebar-link-active' => $active,
                    ])
                >
                    <x-icon :name="$item['icon']" class="h-5 w-5" />
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    <div class="mt-auto space-y-2">
        @foreach ($supportLinks as $link)
            <a href="{{ $link['href'] }}" class="sidebar-link sidebar-link-secondary">
                <x-icon :name="$link['icon']" class="h-5 w-5" />
                <span>{{ $link['label'] }}</span>
            </a>
        @endforeach
    </div>
</aside>
