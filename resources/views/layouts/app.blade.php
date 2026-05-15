<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $pageTitle ?? 'MotoX' }}</title>

        <script>
            (() => {
                const theme = String(localStorage.getItem('theme') || 'light').includes('dark') ? 'dark' : 'light';
                document.documentElement.classList.toggle('dark', theme === 'dark');
                document.documentElement.classList.toggle('light', theme !== 'dark');
            })();
        </script>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="app-shell transition-colors duration-200" data-system-action-status="{{ session('status') ? '1' : '0' }}" data-current-page="{{ $currentPage ?? '' }}">
        @php
            $topbarUser = auth()->user();
            $topbarAvatarUrl = $topbarUser?->avatar_path
                ? \Illuminate\Support\Facades\Storage::url($topbarUser->avatar_path)
                : null;
        @endphp

        <div class="min-h-screen xl:grid" id="main-layout" data-app-layout>
            @include('navbar.sidebar')
            <div id="sidebar-overlay" class="sidebar-overlay hidden xl:hidden" aria-hidden="true"></div>

            <div class="min-w-0">
                @if ($showTopbar ?? true)
                    <header class="sticky top-0 z-20 border-b border-white/60 bg-slate-100/90 backdrop-blur-xl">
                        <div class="flex w-full items-center gap-3 px-4 py-4 sm:px-6 lg:px-8">
                             <div class="ml-auto flex items-center gap-3 sm:gap-4">
                                <div class="header-menu-shell">
                                    <button
                                        class="icon-button"
                                        type="button"
                                        aria-label="Notifications"
                                        title="Notifications"
                                        data-header-menu-trigger="notifications"
                                        aria-expanded="false"
                                    >
                                        <span class="notification-dot-static hidden" data-notification-dot></span>
                                        <span class="notification-count-badge hidden" data-notification-count-badge>0</span>
                                        <x-icon name="bell" class="h-5 w-5" />
                                    </button>
                                    <div class="header-menu-panel notification-menu-panel hidden" data-header-menu-panel="notifications" data-notifications-url="{{ route('notifications.index') }}" data-notifications-read-url="{{ route('notifications.read-all') }}" data-notifications-delete-template="{{ url('/notifications/__ID__') }}">
                                        <div class="flex items-center justify-between gap-3">
                                        <p class="header-menu-title" data-i18n="Notifications">Notifications</p>
                                            <span class="notification-count-pill" data-notification-count-label>0 unread</span>
                                        </div>
                                        <div class="notification-list-scroll mt-3 space-y-2" data-notification-list aria-live="polite">
                                            <p class="text-xs text-slate-500" data-i18n="Loading notifications...">Loading notifications...</p>
                                        </div>
                                        <button type="button" class="header-menu-link w-full text-left" data-mark-notifications-read data-i18n="Mark all as read">Mark all as read</button>
                                        <a href="{{ route('settings') }}#notifications" class="header-menu-link" data-i18n="Notification settings">Notification settings</a>
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
                                        <p class="header-menu-title" data-i18n="Quick Settings">Quick Settings</p>
                                        <a href="{{ route('settings') }}#settings" class="header-menu-link" data-i18n="Open settings page">Open settings page</a>
                                        <a href="{{ route('settings') }}#profile" class="header-menu-link" data-i18n="Edit shop profile">Edit shop profile</a>
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
                                        @if ($topbarAvatarUrl)
                                            <img src="{{ $topbarAvatarUrl }}" alt="{{ $topbarUser?->name ?? 'Profile photo' }}" class="h-8 w-8 rounded-full object-cover">
                                        @else
                                            <x-icon name="user" class="h-5 w-5" />
                                        @endif
                                    </button>
                                    <div class="header-menu-panel hidden" data-header-menu-panel="profile">
                                        <p class="header-menu-title" data-i18n="Profile">Profile</p>
                                        <a href="{{ route('settings') }}#profile" class="header-menu-link" data-i18n="View profile">View profile</a>
                                        <a href="{{ route('settings') }}#settings" class="header-menu-link" data-i18n="Account preferences">Account preferences</a>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('logout') }}" data-logout-form>
                                    @csrf
                                    <button class="icon-button" type="submit" aria-label="Log Out" title="Log Out" data-logout-button>
                                        <x-icon name="logout" class="h-5 w-5" />
                                    </button>
                                </form>
                            </div>
                        </div>

                        @if ($showHeaderSearch ?? true)
                            <div class="pb-5 pl-28 pr-10 lg:hidden">
                                <label class="search-shell flex items-center gap-4">
                                    <x-icon name="search" class="h-5 w-5 text-slate-400" />
                                    <input
                                        type="text"
                                        value=""
                                        placeholder="{{ $searchPlaceholder ?? 'Search anything...' }}"
                                        data-i18n-placeholder="{{ $searchPlaceholder ?? 'Search anything...' }}"
                                        class="w-full border-0 bg-transparent p-0 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-0"
                                    >
                                </label>
                            </div>
                        @endif
                    </header>
                @endif

                <main class="w-full px-4 py-6 sm:px-6 lg:px-8">
                    @yield('content')
                </main>
            </div>
        </div>

        <button
            id="sidebar-fab-toggle"
            type="button"
            class="icon-button sidebar-fab-toggle fixed left-3 top-3 z-40"
            aria-label="Open sidebar"
            title="Open sidebar"
            aria-controls="sidebar"
            data-sidebar-toggle
            data-sidebar-mobile-toggle
        >
            <x-icon name="menu" class="h-5 w-5" />
        </button>

        <div class="logout-loading-overlay hidden" data-logout-overlay aria-live="polite" aria-hidden="true">
            <div class="logout-loading-card">
                <span class="logout-loading-mark">
                    <x-icon name="car" class="h-6 w-6" />
                </span>
                <span class="logout-loading-title" data-i18n="Logging out">Logging out</span>
                <span class="logout-loading-text" data-i18n="logging out...">logging out...</span>
                <span class="logout-loading-bar"><span></span></span>
            </div>
        </div>

        <div class="app-modal hidden" data-confirm-modal>
            <div class="app-modal-card max-w-md">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-2xl font-bold text-slate-900" data-confirm-title>Are you sure?</h3>
                        <p class="mt-2 text-sm text-slate-500" data-confirm-body>You are about to delete this item.</p>
                    </div>
                    <button type="button" class="icon-button" data-confirm-cancel aria-label="Cancel">
                        <x-icon name="x" class="h-4 w-4" />
                    </button>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="ghost-button" data-confirm-cancel data-i18n="Cancel">Cancel</button>
                    <button type="button" class="danger-button" data-confirm-action>
                        <x-icon name="trash" class="h-4 w-4" />
                        <span>Yes, Delete</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="app-modal hidden" data-logout-confirm-modal>
            <div class="app-modal-card max-w-md logout-confirm-card">
                <h3 class="text-2xl font-bold text-slate-900" data-i18n="Are you sure you want to log out?">Are you sure you want to log out?</h3>
                <p class="mt-2 text-sm text-slate-500" data-i18n="You will be logged out of MotoX.">You will be logged out of MotoX.</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="ghost-button" data-cancel-logout data-i18n="Cancel">Cancel</button>
                    <button type="button" class="primary-button" data-confirm-logout data-i18n="Log Out">Log Out</button>
                </div>
            </div>
        </div>

    </body>
</html>
