<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $pageTitle ?? 'MotoX' }}</title>

@vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/sidebar-toggle.js'])
    </head>
    <body class="app-shell transition-colors duration-200">
        <div class="min-h-screen xl:grid xl:grid-cols-[272px_1fr]" id="main-layout">
<button id="sidebar-toggle" class="fixed z-30 xl:hidden left-4 top-20 p-2 bg-white border border-slate-200 shadow-lg rounded-xl hover:shadow-xl hover:bg-slate-50 transition-all" onclick="toggleSidebar()" aria-label="Toggle navigation">
                <x-icon name="menu" class="h-5 w-5 text-slate-700" />
            </button>

            @include('partials.sidebar')

            <div class="min-w-0">
                @if ($showTopbar ?? true)
                    <header class="sticky top-0 z-20 border-b border-white/60 bg-slate-100/90 backdrop-blur-xl">
                        <div class="mx-auto flex max-w-[1600px] items-center gap-3 px-4 py-4 sm:px-6 lg:px-8">
                            <label class="search-shell hidden max-w-xl flex-1 items-center gap-3 lg:flex">
                                <x-icon name="search" class="h-5 w-5 text-slate-400" />
                                <input
                                    type="text"
                                    value=""
                                    placeholder="{{ $searchPlaceholder ?? 'Search anything...' }}"
                                    class="w-full border-0 bg-transparent p-0 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-0"
                                >
                            </label>

                            <div class="ml-auto flex items-center gap-2 sm:gap-3">
                                <a href="{{ route('landing') }}" class="icon-button" aria-label="Back to Landing">
                                    <x-icon name="car" class="h-5 w-5" />
                                </a>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="icon-button" type="submit" aria-label="Log Out">
                                        <x-icon name="logout" class="h-5 w-5" />
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="px-4 pb-4 lg:hidden">
                            <label class="search-shell flex items-center gap-3">
                                <x-icon name="search" class="h-5 w-5 text-slate-400" />
                                <input
                                    type="text"
                                    value=""
                                    placeholder="{{ $searchPlaceholder ?? 'Search anything...' }}"
                                    class="w-full border-0 bg-transparent p-0 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-0"
                                >
                            </label>
                        </div>
                    </header>
                @endif

                <div class="border-b border-white/60 bg-white/70 px-4 py-3 xl:hidden">
                    <div class="no-scrollbar flex gap-2 overflow-x-auto">
                        @foreach ($navigation as $item)
                            @php
                                $active = request()->routeIs($item['route']);
                            @endphp
                            <a
                                href="{{ route($item['route']) }}"
                                @class([
                                    'mobile-nav-chip',
                                    'mobile-nav-chip-active' => $active,
                                ])
                            >
                                <x-icon :name="$item['icon']" class="h-4 w-4" />
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <main class="mx-auto max-w-[1600px] px-4 py-6 sm:px-6 lg:px-8">
                    @yield('content')
                </main>
            </div>
        </div>
    </body>
</html>
