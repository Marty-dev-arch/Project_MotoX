<aside class="app-sidebar relative hidden flex-col border-r px-5 py-6 transition-all duration-300 xl:flex xl:min-h-screen" id="sidebar">
    <div class="sidebar-main space-y-6">
        <div>
            <div class="sidebar-brand-row flex items-center justify-between gap-3">
                <a href="{{ route('landing') }}" title="MotoX" class="sidebar-brand-link inline-flex items-center gap-2 text-2xl font-black tracking-tight transition hover:text-brand-400">
                    <x-icon name="car" class="h-5 w-5 text-brand-600" />
                    <span class="sidebar-text">MotoX</span>
                </a>

                <button
                    id="sidebar-inline-toggle"
                    type="button"
                    class="sidebar-toggle-button hidden xl:inline-flex"
                    aria-label="Close sidebar"
                    title="Close sidebar"
                    data-sidebar-toggle
                >
                    <x-icon name="menu" class="h-5 w-5" />
                </button>
            </div>
            <p class="sidebar-subtitle mt-1 text-sm">Mechanic Workspace</p>
        </div>

        <a href="{{ route('job-orders', ['create' => 1]) }}" class="primary-button sidebar-primary-action w-full" title="New Job Order">
            <x-icon name="plus" class="h-4 w-4" />
            <span class="sidebar-text">New Job Order</span>
        </a>

        <nav class="space-y-1">
            @foreach ($navigation as $item)
                @php
                    $active = request()->routeIs($item['route']);
                @endphp
                <a
                    href="{{ route($item['route']) }}"
                    title="{{ $item['label'] }}"
                    @class([
                        'sidebar-link',
                        'sidebar-link-active' => $active,
                    ])
                >
                    <x-icon :name="$item['icon']" class="h-5 w-5" />
                    <span class="sidebar-text">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    <div class="sidebar-support mt-auto space-y-2">
        @foreach ($supportLinks as $link)
            <a href="{{ $link['href'] }}" class="sidebar-link sidebar-link-secondary" title="{{ $link['label'] }}">
                <x-icon :name="$link['icon']" class="h-5 w-5" />
                <span class="sidebar-text">{{ $link['label'] }}</span>
            </a>
        @endforeach
    </div>
</aside>
