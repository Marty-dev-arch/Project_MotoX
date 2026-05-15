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
    <body class="public-info-page public-legal-page">
        <div class="public-info-shell">
            <header class="public-info-header public-info-header-minimal">
                <a href="{{ route('landing') }}" class="public-back-link">
                    <x-icon name="chevron-left" class="h-4 w-4" />
                    <span>Back to home</span>
                </a>
            </header>

            <main class="flex-1">
                <article class="public-legal-card">
                    <span class="public-legal-icon">
                        <x-icon :name="$icon" class="h-7 w-7" />
                    </span>

                    <h1>{{ $title }}</h1>

                    @if ($updatedAt)
                        <p class="public-legal-updated">Last Updated: {{ $updatedAt }}</p>
                    @endif

                    @if ($description)
                        <p class="public-legal-intro">{{ $description }}</p>
                    @endif

                    <div class="public-legal-sections">
                        @foreach ($sections as $section)
                            <section class="public-legal-section">
                                <h2>{{ $loop->iteration }}. {{ $section['title'] }}</h2>

                                @if (! empty($section['body']))
                                    <p>{{ $section['body'] }}</p>
                                @endif

                                @if (! empty($section['items']))
                                    <ul>
                                        @foreach ($section['items'] as $item)
                                            <li>
                                                @if (is_array($item))
                                                    <strong>{{ $item['label'] }}:</strong>
                                                    <span>{{ $item['text'] }}</span>
                                                @else
                                                    <span>{{ $item }}</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                @if (! empty($section['cards']))
                                    <div class="public-legal-card-list">
                                        @foreach ($section['cards'] as $card)
                                            <article class="public-legal-mini-card">
                                                <div class="public-legal-mini-head">
                                                    <x-icon :name="$card['icon']" class="h-5 w-5" />
                                                    <h3>{{ $card['title'] }}</h3>
                                                </div>
                                                <p>{{ $card['body'] }}</p>
                                            </article>
                                        @endforeach
                                    </div>
                                @endif

                                @if (! empty($section['links']))
                                    <div class="public-legal-link-panel">
                                        <strong>Browser Guides:</strong>
                                        <ul>
                                            @foreach ($section['links'] as $link)
                                                <li><a href="{{ $link['url'] }}" target="_blank" rel="noreferrer">{{ $link['label'] }}</a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if (! empty($section['contact']))
                                    <div class="public-legal-contact-card">
                                        @foreach ($section['contact'] as $line)
                                            <span>{{ $line }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </section>
                        @endforeach
                    </div>
                </article>
            </main>
        </div>
    </body>
</html>
