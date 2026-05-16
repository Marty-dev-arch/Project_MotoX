{{-- Purpose: Renders the sidebar navigation menu. --}}
<aside class="app-sidebar relative hidden h-screen min-h-screen flex-col border-r px-5 py-6 transition-all duration-300 xl:flex" id="sidebar">
    <div class="sidebar-main h-full">
        <div>
            <div class="sidebar-brand-row flex items-center justify-between gap-3">
                <a href="{{ route('landing') }}" title="MotoX" class="sidebar-brand-link inline-flex items-center gap-2 text-2xl font-black tracking-tight transition hover:text-brand-400">
                    <span class="sidebar-brand-icon motox-logo-mark">
                        <x-icon name="car" class="motox-logo-icon" />
                    </span>
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

        <div class="sidebar-utility">
            <a
                href="{{ route('support') }}"
                title="Help Me"
                @class([
                    'sidebar-help-link',
                    'sidebar-link-active' => request()->routeIs('support'),
                ])
            >
                <x-icon name="support" class="h-5 w-5" />
                <span class="sidebar-text" data-i18n="Help Me">Help Me</span>
            </a>

            <button
                type="button"
                class="sidebar-theme-toggle"
                aria-label="Switch theme"
                title="Switch theme"
                data-theme-toggle
                aria-pressed="false"
            >
                <span class="theme-toggle-track">
                    <span class="theme-toggle-thumb">
                        <x-icon name="sun" class="theme-toggle-icon theme-toggle-icon-light h-3.5 w-3.5" />
                        <x-icon name="moon" class="theme-toggle-icon theme-toggle-icon-dark h-3.5 w-3.5" />
                    </span>
                </span>
            </button>
        </div>
    </div>

</aside>
