@extends('layouts.auth')

@section('content')
    <div class="landing-refresh-loader" data-landing-loader aria-live="polite">
        <div class="landing-refresh-loader-card">
            <span class="landing-refresh-mark motox-logo-mark">
                <x-icon name="car" class="motox-logo-icon" />
            </span>
            <span class="landing-refresh-brand">MotoX</span>
            <span class="landing-refresh-text"> </span>
            <span class="landing-refresh-progress"><span></span></span>
        </div>
    </div>

    <div id="top" class="landing-page landing-mark-page" data-landing-metrics-url="{{ $landingMetricsUrl }}">
        <div class="landing-mark-shell">
            <header class="landing-header landing-mark-header">
                <a href="{{ route('landing') }}" class="landing-brand">
                    <span class="landing-brand-icon motox-logo-mark">
                        <x-icon name="car" class="motox-logo-icon" />
                    </span>
                    <span class="landing-brand-name">MotoX</span>
                </a>

                <nav class="landing-nav" aria-label="Landing navigation">
                    <a href="#features">Features</a>
                    <a href="#modules">Modules</a>
                    <a href="#about">Workflow</a>
                </nav>

                <div class="landing-top-actions">
                    <a href="{{ route('login') }}" class="ghost-button landing-login-button px-5 py-2.5">Log In</a>
                    <a href="{{ $primaryCtaRoute }}" class="primary-button landing-get-started-button px-6 py-2.5">{{ $primaryCtaLabel }}</a>
                </div>
            </header>

            <main>
                <section class="landing-hero landing-mark-hero landing-reveal" style="--landing-delay: 30ms;">
                    <div class="landing-mark-hero-copy">
                        <h1>
                            Grow your MotorShop with
                            <span>Accurate operation Data.</span>
                        </h1>

                        <p>
                            MotoX connects Dashboard, Inventory, Job Orders, Billing, Reports, and Customers using your
                            actual records in one connected workspace.
                        </p>

                        <div class="landing-hero-actions">
                            <a href="{{ $primaryCtaRoute }}" class="primary-button landing-get-started-button px-8 py-4">Get Started</a>
                            <a href="{{ $secondaryCtaRoute }}" class="landing-inline-link">
                                {{ $secondaryCtaLabel }}
                                <x-icon name="chevron-right" class="h-4 w-4" />
                            </a>
                        </div>
                    </div>

                    <div class="landing-mark-preview">
                        <div class="landing-picture-stage">
                            <img
                                src="{{ asset('images/landing/motorcycle-repair.jpg') }}"
                                alt="Technician repairing a motorcycle engine"
                                fetchpriority="high"
                                decoding="async"
                            >
                            <img
                                src="{{ asset('images/landing/service-consultation.jpg') }}"
                                alt="Service consultation inside a motorcycle workshop"
                                loading="lazy"
                                decoding="async"
                            >
                            <img
                                src="{{ asset('images/landing/tyre-service.webp') }}"
                                alt="Motorcycle tyre service in progress"
                                loading="lazy"
                                decoding="async"
                            >
                        </div>
                    </div>
                </section>

                <section id="features" class="landing-mark-section landing-reveal" style="--landing-delay: 110ms;">
                    <div class="landing-mark-section-head">
                        <p>## Modules</p>
                        <h2>Production modules, real-time monitor.</h2>
                    </div>

                    <div class="landing-feature-grid">
                        @foreach ($moduleHighlights as $module)
                            <article class="landing-feature-card">
                                <span class="icon-chip h-12 w-12 rounded-2xl bg-slate-100 text-brand-700">
                                    <x-icon :name="$module['icon']" class="h-6 w-6" />
                                </span>
                                <h3>{{ $module['title'] }}</h3>
                                <p>{{ $module['description'] }}</p>
                                <a href="{{ route($module['route']) }}" class="landing-inline-link">
                                    Open {{ $module['title'] }}
                                    <x-icon name="chevron-right" class="h-4 w-4" />
                                </a>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section id="modules" class="landing-mark-section landing-reveal" style="--landing-delay: 170ms;">
                    <div class="landing-mark-section-head">
                        <p>## Operational Health</p>
                        <h2>Current shop activity at a glance.</h2>
                    </div>

                    <div class="landing-pricing-grid">
                        @foreach ($timeWindows as $window)
                            <article class="landing-pricing-card">
                                <p class="landing-plan-name">{{ $window['label'] }}</p>
                                <p class="landing-plan-price" data-landing-value="{{ $window['key'] }}">{{ $window['value'] }}</p>
                                <p class="landing-plan-copy">{{ $window['note'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section id="about" class="landing-mark-section landing-mark-workflow landing-reveal" style="--landing-delay: 220ms;">
                    <div class="landing-mark-section-head">
                        <p>## Workflow</p>
                        <h2>Built around real garage handoffs.</h2>
                    </div>

                    <div class="landing-workflow-grid">
                        <article>
                            <span>01</span>
                            <h3>Track the record.</h3>
                            <p>Use Customers and Job Orders to keep every service connected to the right rider, vehicle, and status.</p>
                        </article>
                        <article>
                            <span>02</span>
                            <h3>Move the stock.</h3>
                            <p>Inventory changes update stock health, alerts, and reporting from the same operational source.</p>
                        </article>
                        <article>
                            <span>03</span>
                            <h3>Close with proof.</h3>
                            <p>Billing and reports summarize the same records your team used during the repair.</p>
                        </article>
                    </div>
                </section>

                <footer id="contact" class="landing-footer landing-reveal" style="--landing-delay: 280ms;">
                    <div class="landing-footer-main">
                        <a href="{{ route('landing') }}" class="landing-brand">
                            <span class="landing-brand-icon motox-logo-mark">
                                <x-icon name="car" class="motox-logo-icon" />
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
                            <a href="{{ route('privacy') }}">Privacy</a>
                            <a href="{{ route('cookies') }}">Cookies</a>
                            <a href="{{ route('support') }}">Help Me</a>
                        </div>
                    </div>
                </footer>
            </main>
        </div>
    </div>
@endsection
