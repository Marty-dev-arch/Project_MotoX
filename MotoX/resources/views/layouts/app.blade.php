<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $pageTitle ?? 'MotoX' }}</title>

@vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="app-shell transition-colors duration-200">
        <div class="min-h-screen xl:grid xl:grid-cols-[272px_1fr]" id="main-layout" data-app-layout>
            <button id="sidebar-toggle" class="fixed left-4 top-20 z-30 rounded-xl border border-slate-200 bg-white p-2 shadow-lg transition-all hover:bg-slate-50 hover:shadow-xl xl:hidden" aria-label="Toggle navigation">
                <x-icon name="menu" class="h-5 w-5 text-slate-700" />
            </button>

            @include('partials.sidebar')

            <div class="min-w-0">
                @if ($showTopbar ?? true)
                    <header class="sticky top-0 z-20 border-b border-white/60 bg-slate-100/90 backdrop-blur-xl">
                        <div class="mx-auto flex max-w-[1600px] items-center gap-3 px-4 py-4 sm:px-6 lg:px-8">
                            <button id="sidebar-desktop-toggle" type="button" class="icon-button hidden xl:inline-flex" aria-label="Hide sidebar" title="Hide sidebar">
                                <x-icon name="menu" class="h-5 w-5" />
                            </button>
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
                                <div class="header-menu-shell">
                                    <button
                                        class="icon-button"
                                        type="button"
                                        aria-label="Notifications"
                                        title="Notifications"
                                        data-header-menu-trigger="notifications"
                                        aria-expanded="false"
                                    >
                                        <span class="absolute right-2 top-2 h-2 w-2 rounded-full bg-brand-500" data-notification-dot></span>
                                        <x-icon name="bell" class="h-5 w-5" />
                                    </button>
                                    <div class="header-menu-panel hidden" data-header-menu-panel="notifications">
                                        <p class="header-menu-title">Notifications</p>
                                        <div class="header-menu-item">
                                            <x-icon name="alert" class="h-4 w-4 text-brand-500" />
                                            <div>
                                                <p class="header-menu-label">Low stock alert</p>
                                                <p class="header-menu-text">Brake pads are nearing minimum stock.</p>
                                            </div>
                                        </div>
                                        <div class="header-menu-item">
                                            <x-icon name="job-orders" class="h-4 w-4 text-sky-500" />
                                            <div>
                                                <p class="header-menu-label">Job order update</p>
                                                <p class="header-menu-text">Two vehicles were marked ready for release.</p>
                                            </div>
                                        </div>
                                        <button type="button" class="header-menu-link w-full text-left" data-mark-notifications-read>Mark all as read</button>
                                        <a href="{{ route('settings') }}#notifications" class="header-menu-link">Notification settings</a>
                                    </div>
                                </div>

                                <div class="header-menu-shell">
                                    <button
                                        class="icon-button"
                                        type="button"
                                        aria-label="Settings"
                                        title="Settings"
                                        data-header-menu-trigger="settings"
                                        aria-expanded="false"
                                    >
                                        <x-icon name="settings" class="h-5 w-5" />
                                    </button>
                                    <div class="header-menu-panel hidden" data-header-menu-panel="settings">
                                        <p class="header-menu-title">Quick Settings</p>
                                        <a href="{{ route('settings') }}#settings" class="header-menu-link">Open settings page</a>
                                        <a href="{{ route('settings') }}#profile" class="header-menu-link">Edit shop profile</a>
                                    </div>
                                </div>

                                <div class="header-menu-shell">
                                    <button
                                        class="icon-button"
                                        type="button"
                                        aria-label="Profile"
                                        title="Profile"
                                        data-header-menu-trigger="profile"
                                        aria-expanded="false"
                                    >
                                        <x-icon name="user" class="h-5 w-5" />
                                    </button>
                                    <div class="header-menu-panel hidden" data-header-menu-panel="profile">
                                        <p class="header-menu-title">Profile</p>
                                        <a href="{{ route('settings') }}#profile" class="header-menu-link">View profile</a>
                                        <a href="{{ route('settings') }}#settings" class="header-menu-link">Account preferences</a>
                                    </div>
                                </div>

                                <a href="{{ route('landing') }}" class="icon-button" aria-label="Back to Landing" title="Back to Landing">
                                    <x-icon name="car" class="h-5 w-5" />
                                </a>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="icon-button" type="submit" aria-label="Log Out" title="Log Out">
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

                <main class="mx-auto max-w-[1600px] px-4 py-6 sm:px-6 lg:px-8">
                    @yield('content')
                </main>
            </div>
        </div>
    </body>
</html>
