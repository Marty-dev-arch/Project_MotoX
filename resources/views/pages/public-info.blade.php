<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
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
    <body class="public-info-page">
        <div class="public-info-shell">
            <header class="public-info-header">
                <a href="{{ route('landing') }}" class="sidebar-brand-link inline-flex items-center gap-2 text-2xl font-black tracking-tight">
                    <span class="sidebar-brand-icon">
                        <x-icon name="car" class="h-5 w-5" />
                    </span>
                    <span>MotoX</span>
                </a>

                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        class="theme-toggle-button"
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

                    <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="ghost-button">
                        {{ auth()->check() ? 'Dashboard' : 'Log In' }}
                    </a>
                </div>
            </header>

            <main class="flex-1">
                <section class="public-info-hero">
                    <div>
                        <p class="public-info-eyebrow">{{ $eyebrow }}</p>
                        <h1>{{ $title }}</h1>
                        <p>{{ $description }}</p>
                    </div>
                    <div class="public-info-hero-panel">
                        <span class="public-info-hero-icon">
                            <x-icon :name="$support ? 'support' : 'file'" class="h-6 w-6" />
                        </span>
                        <p>{{ $support ? 'Step-by-step workspace guidance for daily shop tasks.' : 'Plain-language information for customers, staff, and workshop owners.' }}</p>
                    </div>
                </section>

                <section class="public-info-grid public-info-grid-expanded">
                    @foreach ($sections as $section)
                        <article class="public-info-card">
                            <span class="public-info-card-number">{{ str_pad((string) ($loop->iteration), 2, '0', STR_PAD_LEFT) }}</span>
                            <h2>{{ $section['title'] }}</h2>
                            <p>{{ $section['body'] }}</p>
                        </article>
                    @endforeach
                </section>

                <section class="public-info-detail-band">
                    <div>
                        <p class="public-info-eyebrow">{{ $support ? 'How to use Help Me' : 'How MotoX treats this page' }}</p>
                        <h2>{{ $support ? 'Find the page, check the record, then take action.' : 'Clear information, direct links, and practical shop context.' }}</h2>
                    </div>
                    <div class="public-info-detail-list">
                        <p>{{ $support ? 'Use the sidebar to move between pages. Use search and date filters to narrow records before editing or downloading receipts.' : 'These pages explain how MotoX supports real workshop operations, from secure sign-in to customer records and browser preferences.' }}</p>
                        <p>{{ $support ? 'When you need to report an issue, include the page name, customer or invoice, what button was clicked, and what appeared on screen.' : 'Return here from the footer any time you need to review policies, privacy handling, cookies, or the help guide.' }}</p>
                    </div>
                </section>

                @if ($support)
                    <section class="support-console">
                        <article class="support-chat-card">
                            <div class="support-chat-head">
                                <span class="support-status-dot"></span>
                                <div>
                                    <h2 class="support-chat-title">Quick Help Workflow</h2>
                                    <p class="support-chat-copy">Follow these steps when something is unclear or blocked.</p>
                                </div>
                            </div>

                            <div class="support-step-grid">
                                <div class="support-message support-message-agent">
                                    <strong>Step 1</strong>
                                    Pick the page you are working on: Dashboard, Inventory, Customers, Job Orders, Billing, Reports, Logs, or Settings.
                                </div>
                                <div class="support-message support-message-user">
                                    <strong>Step 2</strong>
                                    Use search and date filters before editing records so you are working on the correct customer, invoice, job order, or part.
                                </div>
                                <div class="support-message support-message-agent">
                                    <strong>Step 3</strong>
                                    If something still looks wrong, check the current page, exact record name, date filter, status, and latest Logs.
                                </div>
                            </div>

                            <div class="support-chat-actions">
                                <a href="{{ route('dashboard') }}" class="primary-button">Open Dashboard</a>
                                <a href="{{ route('billing') }}" class="ghost-button">Open Billing</a>
                                <a href="{{ route('settings') }}" class="ghost-button">Open Settings</a>
                            </div>
                        </article>
                    </section>
                @endif
            </main>

            <footer class="landing-footer public-info-footer mt-6">
                <div class="landing-footer-main">
                    <div>
                        <p class="landing-footer-brand">MotoX</p>
                        <p class="landing-footer-copy">Workshop tools for inventory, customers, job orders, billing receipts, reports, and daily shop operations.</p>
                    </div>
                    <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="primary-button">
                        {{ auth()->check() ? 'Open Dashboard' : 'Get Started' }}
                    </a>
                </div>
                <div class="landing-footer-bottom">
                    <p class="landing-footer-copy">&copy; {{ now()->year }} MotoX Inc. All rights reserved.</p>
                    <div class="landing-footer-links">
                        <a href="{{ route('policies') }}" @class(['landing-footer-link-active' => request()->routeIs('policies')])>Policies</a>
                        <a href="{{ route('privacy') }}" @class(['landing-footer-link-active' => request()->routeIs('privacy')])>Privacy</a>
                        <a href="{{ route('cookies') }}" @class(['landing-footer-link-active' => request()->routeIs('cookies')])>Cookies</a>
                        <a href="{{ route('support') }}" @class(['landing-footer-link-active' => request()->routeIs('support')])>Help Me</a>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
