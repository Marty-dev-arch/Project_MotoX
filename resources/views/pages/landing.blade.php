@extends('layouts.auth')

@section('content')
    <div class="landing-refresh-loader" data-landing-loader aria-live="polite">
        <div class="landing-refresh-loader-card">
            <span class="landing-refresh-mark">
                <x-icon name="car" class="h-7 w-7" />
            </span>
            <span class="landing-refresh-brand">MotoX</span>
            <span class="landing-refresh-text"> please wait. Loading.... </span>
            <span class="landing-refresh-progress"><span></span></span>
        </div>
    </div>

    <div id="top" class="landing-page" data-landing-metrics-url="{{ $landingMetricsUrl }}">
        <div class="mx-auto w-full max-w-[1600px] px-3 py-4 sm:px-5 lg:px-6">
            <header class="landing-header">
                <a href="{{ route('landing') }}" class="landing-brand">
                    <span class="landing-brand-icon">
                        <x-icon name="car" class="h-5 w-5" />
                    </span>
                    <span class="landing-brand-name">MotoX</span>
                </a>

                <nav class="landing-nav" aria-label="Landing navigation">
                    <a href="#features">Features</a>
                    <a href="#modules">Modules</a>
                    <a href="#about">About</a>
                    <a href="#contact">Contact</a>
                </nav>

                <div class="landing-top-actions">
                    <a href="{{ route('login') }}" class="ghost-button landing-login-button px-5 py-2.5">Log In</a>
                    <a href="{{ $primaryCtaRoute }}" class="primary-button px-6 py-2.5">{{ $primaryCtaLabel }}</a>
                </div>
            </header>

            <section class="landing-hero landing-reveal mt-4" style="--landing-delay: 30ms;">
                <div class="landing-hero-grid">
                    <div class="landing-hero-copy">
                        <p class="landing-hero-tag">
                            <span class="landing-dot"></span>
                            Live Data &middot; Workshop Time
                        </p>

                        <h1 class="mt-6 text-5xl font-black tracking-tight text-slate-900 sm:text-6xl lg:text-6xl">
                            Grow your MotorShop with
                            <span class="text-brand-600">Accurate operation Data.</span>
                            
                        </h1>

                        <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600">
                            MotoX connects Dashboard, Inventory, Job Orders, Billing, Reports, and Customers using your
                            actual records in one connected workspace.
                        </p>

                        <div class="landing-hero-actions mt-7 flex flex-wrap gap-3">
                            <a href="{{ $primaryCtaRoute }}" class="primary-button landing-get-started-button px-8 py-4">Get Started</a>
                            <a href="{{ $secondaryCtaRoute }}" class="landing-inline-link">
                                {{ $secondaryCtaLabel }}
                                <x-icon name="chevron-right" class="h-4 w-4" />
                            </a>
                        </div>

                        <div class="landing-kpi-grid mt-7">
                            @foreach ($projectSnapshot as $metric)
                                <article class="landing-kpi-card">
                                    <p class="landing-kpi-label">{{ $metric['label'] }}</p>
                                    <p class="landing-kpi-value" data-landing-value="{{ $metric['key'] }}">{{ $metric['value'] }}</p>
                                    <p class="landing-kpi-note">{{ $metric['note'] }}</p>
                                </article>
                            @endforeach
                        </div>
                    </div>

                    <div class="landing-hero-visual">
                        <div class="landing-hero-photo-shell landing-image-slider">
                            <div class="landing-slider-track">
                                <img
                                    src="{{ asset('images/landing/motorcycle-repair.jpg') }}"
                                    alt="Technician repairing a motorcycle engine"
                                    class="landing-hero-photo"
                                    fetchpriority="high"
                                    decoding="async"
                                >
                            </div>
                        </div>

                        <article class="landing-float-card landing-float-card-top">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Workspace Pulse</p>
                            <div class="mt-3 space-y-3">
                                @foreach ($workspacePulse as $pulse)
                                    <div class="landing-mini-row">
                                        <span>{{ $pulse['title'] }}</span>
                                        <x-badge :tone="$pulse['tone']" data-landing-value="{{ $pulse['key'] }}">{{ $pulse['value'] }}</x-badge>
                                    </div>
                                @endforeach
                            </div>
                        </article>

                        <article class="landing-float-card landing-float-card-bottom">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Last Sync</p>
                            <p class="mt-2 text-sm font-semibold text-slate-700" data-landing-updated>{{ $landingUpdatedAt }}</p>
                            <div class="mt-3 grid grid-cols-2 gap-3">
                                @foreach (collect($timeWindows)->take(2) as $window)
                                    <div class="landing-fact-chip">
                                        <p class="landing-fact-value" data-landing-value="{{ $window['key'] }}">{{ $window['value'] }}</p>
                                        <p class="landing-fact-label">{{ $window['label'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </article>

                    </div>
                </div>
            </section>

            <section id="features" class="landing-section landing-reveal mt-4" style="--landing-delay: 110ms;">
                <div class="mx-auto max-w-3xl text-center">
                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-brand-700">Core Workspace</p>
                    <h2 class="mt-3 text-4xl font-black tracking-tight text-slate-900 sm:text-5xl">Production Modules, Real-Time Monitor</h2>
                    <p class="mt-3 text-lg leading-8 text-slate-600">
                        Each module below maps to a real MotoX page where users can track live changes.
                    </p>
                </div>

                <div class="landing-feature-grid mt-10">
                    @foreach ($moduleHighlights as $module)
                        <article class="landing-feature-card">
                            <span class="icon-chip h-12 w-12 rounded-2xl bg-slate-100 text-brand-700">
                                <x-icon :name="$module['icon']" class="h-6 w-6" />
                            </span>
                            <h3 class="mt-4 text-2xl font-bold tracking-tight text-slate-900">{{ $module['title'] }}</h3>
                            <p class="mt-3 text-base leading-7 text-slate-600">
                                {{ $module['description'] }}
                            </p>
                            <a href="{{ route($module['route']) }}" class="landing-inline-link mt-5">
                                Open {{ $module['title'] }}
                                <x-icon name="chevron-right" class="h-4 w-4" />
                            </a>
                        </article>
                    @endforeach
                </div>
            </section>

            <section id="modules" class="landing-section landing-reveal mt-4" style="--landing-delay: 170ms;">
                <div class="mx-auto max-w-3xl text-center">
                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-brand-700">Operational Health</p>
                    <h2 class="mt-3 text-4xl font-black tracking-tight text-slate-900 sm:text-5xl">Real-Time Project Window</h2>
                    <p class="mt-3 text-lg leading-8 text-slate-600">
                        The summary below updates from inventory movements, closed jobs, and billing data.
                    </p>
                </div>

                <div class="landing-pricing-grid mt-10">
                    @foreach ($timeWindows as $window)
                        <article class="landing-pricing-card">
                            <p class="landing-plan-name">{{ $window['label'] }}</p>
                            <p class="landing-plan-price" data-landing-value="{{ $window['key'] }}">{{ $window['value'] }}</p>
                            <p class="landing-plan-copy">{{ $window['note'] }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            <section id="about" class="landing-section landing-reveal mt-4" style="--landing-delay: 220ms;">
                <div class="landing-mission-grid">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.28em] text-brand-700">Our Mission</p>
                        <h2 class="mt-3 text-4xl font-black tracking-tight text-slate-900 sm:text-5xl">Precision Software for Real Workshop Conditions.</h2>
                        <p class="mt-5 text-lg leading-8 text-slate-600">
                            MotoX is designed around real garage workflows: fast updates, visible status, reliable records,
                            and clean handoffs between mechanics, service advisors, and billing staff.
                        </p>

                        <div class="mt-8 space-y-4">
                            <div class="landing-check-item">
                                <span class="landing-check-icon">
                                    <x-icon name="check-circle" class="h-4 w-4" />
                                </span>
                                <span>Dashboard and reports powered by recorded operational data</span>
                            </div>
                            <div class="landing-check-item">
                                <span class="landing-check-icon">
                                    <x-icon name="check-circle" class="h-4 w-4" />
                                </span>
                                <span>Inventory movement and valuation with Philippine-time visibility</span>
                            </div>
                            <div class="landing-check-item">
                                <span class="landing-check-icon">
                                    <x-icon name="check-circle" class="h-4 w-4" />
                                </span>
                                <span>Consistent workflow from check-in to completed invoice</span>
                            </div>
                        </div>
                    </div>

                    <div class="landing-photo-panel">
                        <img
                            src="https://repairsmith-prod-wordpress.s3.amazonaws.com/2022/11/mechanic-working-on-engine.jpg"
                            alt="Mechanic working on an engine"
                            class="landing-photo"
                            loading="lazy"
                            decoding="async"
                        >
                        <div class="landing-art-quote">
                            <p class="text-2xl font-bold tracking-tight text-slate-900">"From service bay to invoice, every detail stays traceable."</p>
                            <p class="mt-2 text-sm text-slate-500">Built for reliable execution, not presentation-only dashboards.</p>
                        </div>
                    </div>
                </div>
            </section>

            <footer id="contact" class="landing-footer landing-reveal mt-4" style="--landing-delay: 280ms;">
                <div class="landing-footer-main">
                    <a href="{{ route('landing') }}" class="landing-brand">
                        <span class="landing-brand-icon">
                            <x-icon name="car" class="h-5 w-5" />
                        </span>
                        <span class="landing-brand-name">MotoX</span>
                    </a>
                    <p class="landing-footer-copy">
                        Professional workshop software for job orders, inventory, billing, customers, and real reports.
                    </p>
                </div>

                <div class="landing-footer-bottom">
                    <p class="landing-footer-copy">&copy; {{ now()->year }} MotoX Inc. All rights reserved.</p>
                    <div class="landing-social-links" aria-label="Social links">
                        <a href="https://www.instagram.com/zayezouxy_?igsh=bTQ1cXN3aDhrZG12" target="_blank" rel="noopener noreferrer" aria-label="MotoX Instagram"><x-icon name="instagram" class="h-4 w-4" /></a>
                        <a href="https://www.facebook.com/share/1Hf2JxDwop/" target="_blank" rel="noopener noreferrer" aria-label="MotoX Facebook"><x-icon name="facebook" class="h-4 w-4" /></a>
                        <a href="https://www.tiktok.com/@roeyycy123" target="_blank" rel="noopener noreferrer" aria-label="MotoX TikTok"><x-icon name="tiktok" class="h-4 w-4" /></a>
                    </div>
                    <div class="landing-footer-links">
                        <a href="#contact">Terms</a>
                        <a href="#contact">Privacy</a>
                        <a href="#contact">Cookies</a>
                    </div>
                </div>

                <a href="#top" class="landing-back-top" aria-label="Back to top">
                    <x-icon name="arrow-up" class="h-5 w-5" />
                </a>
            </footer>
        </div>
    </div>
@endsection
