@extends('layouts.auth')

@section('content')
    <div class="landing-page">
        <div class="mx-auto max-w-[1360px] px-4 py-6 sm:px-6 lg:px-8">
            <header class="landing-header">
                <a href="{{ route('landing') }}" class="landing-brand">
                    <span class="landing-brand-icon">
                        <x-icon name="car" class="h-5 w-5" />
                    </span>
                    <span class="landing-brand-name">MotoX</span>
                </a>

                <nav class="landing-nav" aria-label="Landing navigation">
                    <a href="#features">Features</a>
                    <a href="#pricing">Pricing</a>
                    <a href="#about">About</a>
                    <a href="#contact">Contact</a>
                </nav>

                <div class="landing-top-actions">
                    <a href="{{ route('login') }}" class="ghost-button px-5 py-2.5">Log In</a>
                    <a href="{{ route('register') }}" class="primary-button px-6 py-2.5">Sign Up</a>
                </div>
            </header>

            <section class="landing-hero mt-6">
                <div class="landing-hero-grid">
                    <div>
                        <p class="landing-hero-tag">
                            <span class="landing-dot"></span>
                            Automotive Management
                        </p>

                        <h1 class="mt-9 text-5xl font-black tracking-tight text-slate-900 sm:text-6xl lg:text-7xl">
                            Grow your
                            <span class="text-brand-600">workshop's</span>
                            potential.
                        </h1>

                        <p class="mt-6 max-w-2xl text-xl leading-9 text-slate-600">
                            Move beyond spreadsheets. MotoX delivers high-end editorial clarity to inventory,
                            work orders, and customer relations, bringing structural perfection to the modern garage.
                        </p>
                    </div>

                    <div class="landing-order-board">
                        <div class="flex items-center justify-between gap-4">
                            <h2 class="text-4xl font-bold tracking-tight text-slate-900">Active Work Orders</h2>
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-emerald-700">Live</span>
                        </div>

                        <div class="mt-7 space-y-4">
                            <article class="landing-order-item">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-3xl font-semibold tracking-tight text-slate-900">Porsche 911 Carrera</p>
                                        <p class="mt-1 text-lg text-slate-500">Engine Diagnostics</p>
                                    </div>
                                    <p class="text-3xl font-black tracking-tight text-brand-700">P2586</p>
                                </div>
                                <div class="mt-3">
                                    <x-badge tone="success">In Progress</x-badge>
                                </div>
                            </article>

                            <article class="landing-order-item">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-3xl font-semibold tracking-tight text-slate-900">BMW M3</p>
                                        <p class="mt-1 text-lg text-slate-500">Suspension Tune</p>
                                    </div>
                                    <p class="text-3xl font-black tracking-tight text-slate-900">P778</p>
                                </div>
                                <div class="mt-3">
                                    <x-badge>Scheduled</x-badge>
                                </div>
                            </article>
                        </div>

                        <div class="landing-efficiency mt-6">
                            <span class="icon-chip h-11 w-11 rounded-full bg-emerald-200 text-emerald-700">
                                <x-icon name="trend" class="h-5 w-5" />
                            </span>
                            <div>
                                <p class="text-sm text-slate-500">Efficiency Increase</p>
                                <p class="text-4xl font-black tracking-tight text-slate-900">+34%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="features" class="landing-section mt-6">
                <div class="mx-auto max-w-3xl text-center">
                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-brand-700">The Platform</p>
                    <h2 class="mt-4 text-5xl font-black tracking-tight text-slate-900">Precise in Every Module</h2>
                    <p class="mt-4 text-xl leading-9 text-slate-600">
                        A toolkit designed to bring order, transparency, and high-end aesthetic to your daily operations.
                    </p>
                </div>

                <div class="mt-10 grid gap-5 md:grid-cols-3">
                    <article class="landing-module-card">
                        <span class="icon-chip h-12 w-12 rounded-2xl bg-slate-100 text-brand-700">
                            <x-icon name="inventory" class="h-6 w-6" />
                        </span>
                        <h3 class="mt-6 text-4xl font-bold tracking-tight text-slate-900">The Part Ledger</h3>
                        <p class="mt-4 text-lg leading-8 text-slate-600">
                            Stop guessing on margins. Our Part Ledger cross-references live pricing with your inventory,
                            styled like a physical workshop folder for instant clarity.
                        </p>
                        <a href="{{ route('register') }}" class="mt-7 inline-flex items-center gap-2 text-base font-semibold text-brand-700 hover:text-brand-800">
                            Explore Inventory
                            <x-icon name="chevron-right" class="h-4 w-4" />
                        </a>
                    </article>

                    <article class="landing-module-card">
                        <span class="icon-chip h-12 w-12 rounded-2xl bg-slate-100 text-brand-700">
                            <x-icon name="wrench" class="h-6 w-6" />
                        </span>
                        <h3 class="mt-6 text-4xl font-bold tracking-tight text-slate-900">Editorial Work Orders</h3>
                        <p class="mt-4 text-lg leading-8 text-slate-600">
                            Transform messy repair tickets into clean, readable digital documents.
                            High-contrast typography ensures mechanics see crucial specs at a glance.
                        </p>
                        <a href="{{ route('register') }}" class="mt-7 inline-flex items-center gap-2 text-base font-semibold text-brand-700 hover:text-brand-800">
                            View Work Orders
                            <x-icon name="chevron-right" class="h-4 w-4" />
                        </a>
                    </article>

                    <article class="landing-module-card">
                        <span class="icon-chip h-12 w-12 rounded-2xl bg-slate-100 text-brand-700">
                            <x-icon name="customers" class="h-6 w-6" />
                        </span>
                        <h3 class="mt-6 text-4xl font-bold tracking-tight text-slate-900">Client Transparency</h3>
                        <p class="mt-4 text-lg leading-8 text-slate-600">
                            Generate beautiful, easy-to-understand invoices and service reports that build trust.
                            Send updates seamlessly directly to their devices.
                        </p>
                        <a href="{{ route('register') }}" class="mt-7 inline-flex items-center gap-2 text-base font-semibold text-brand-700 hover:text-brand-800">
                            See CRM Tools
                            <x-icon name="chevron-right" class="h-4 w-4" />
                        </a>
                    </article>
                </div>
            </section>

            <section id="pricing" class="landing-section mt-6">
                <div class="mx-auto max-w-3xl text-center">
                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-brand-700">Pricing</p>
                    <h2 class="mt-4 text-5xl font-black tracking-tight text-slate-900">Built for Local Shops</h2>
                    <p class="mt-4 text-xl leading-9 text-slate-600">
                        No trial gimmicks and no lock-in. Register once and run your real operations on your own data.
                    </p>
                </div>
            </section>

            <section id="about" class="landing-section mt-6">
                <div class="landing-mission-grid">
                    <div class="landing-photo-panel">
                        <img
                            src="https://images.unsplash.com/photo-1487754180451-c456f719a1fc?auto=format&fit=crop&w=1400&q=80"
                            alt="Mechanic working on an engine"
                            class="landing-photo"
                        >
                        <div class="landing-art-quote">
                            <p class="text-3xl font-bold tracking-tight text-slate-900">"Built for the builders."</p>
                            <p class="mt-2 text-sm text-slate-500">We understand the grease and the glory. We just made the paperwork cleaner.</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.28em] text-brand-700">Our Mission</p>
                        <h2 class="mt-4 text-5xl font-black tracking-tight text-slate-900">Empowering Local Garages with Enterprise Power.</h2>
                        <p class="mt-6 text-xl leading-9 text-slate-600">
                            The automotive repair industry is built on precision, expertise, and trust.
                            MotoX strips away clutter so you can focus on the craft, not the software.
                        </p>

                        <div class="mt-8 space-y-4">
                            <div class="landing-check-item">
                                <span class="landing-check-icon">
                                    <x-icon name="check-circle" class="h-4 w-4" />
                                </span>
                                <span>Designed for high-density clarity</span>
                            </div>
                            <div class="landing-check-item">
                                <span class="landing-check-icon">
                                    <x-icon name="check-circle" class="h-4 w-4" />
                                </span>
                                <span>Eliminating operational friction</span>
                            </div>
                            <div class="landing-check-item">
                                <span class="landing-check-icon">
                                    <x-icon name="check-circle" class="h-4 w-4" />
                                </span>
                                <span>Elevating the customer experience</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <footer id="contact" class="landing-footer mt-6">
                <a href="{{ route('landing') }}" class="landing-brand">
                    <span class="landing-brand-icon">
                        <x-icon name="car" class="h-5 w-5" />
                    </span>
                    <span class="landing-brand-name">MotoX</span>
                </a>

                <div class="landing-footer-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Cookie Policy</a>
                </div>
            </footer>
        </div>
    </div>
@endsection
