<?php

namespace App\Http\Controllers\Concerns;

use App\Support\WorkshopDemo;

trait BuildsPageData
{
    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function buildPageData(string $currentPage, array $data, bool $showTopbar = true): array
    {
        $user = auth()->user();
        $shop = $user?->shop;

        return array_merge([
            'pageTitle' => $data['heading'] ?? 'MotoX',
            'navigation' => WorkshopDemo::navigation(),
            'supportLinks' => WorkshopDemo::supportLinks(),
            'currentPage' => $currentPage,
            'currentUser' => [
                'name' => $user?->name ?? 'MotoX',
                'role' => $shop?->name ?? 'Workshop',
                'initials' => collect(explode(' ', $user?->name ?? 'MX'))
                    ->filter()
                    ->map(fn (string $part): string => mb_substr($part, 0, 1))
                    ->take(2)
                    ->implode(''),
                'online' => true,
            ],
            'showTopbar' => $showTopbar,
        ], $data);
    }
}

