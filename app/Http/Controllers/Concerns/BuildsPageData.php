<?php

namespace App\Http\Controllers\Concerns;

trait BuildsPageData
{
    
    protected function buildPageData(string $currentPage, array $data, bool $showTopbar = true): array
    {
        $user = auth()->user();
        $shop = $user?->shop;

        return array_merge([
            'pageTitle' => $data['heading'] ?? 'MotoX',
            'navigation' => $this->navigationItems(),
            'supportLinks' => $this->supportItems(),
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

    /**
     * @return array<int, array{label:string,route:string,icon:string}>
     */
    private function navigationItems(): array
    {
        return [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'dashboard'],
            ['label' => 'Customers', 'route' => 'customers', 'icon' => 'customers'],
            ['label' => 'Job Orders', 'route' => 'job-orders', 'icon' => 'job-orders'],
            ['label' => 'Inventory', 'route' => 'inventory', 'icon' => 'inventory'],
            ['label' => 'Billing', 'route' => 'billing', 'icon' => 'billing'],
            ['label' => 'Reports', 'route' => 'reports', 'icon' => 'reports'],
            ['label' => 'Settings', 'route' => 'settings', 'icon' => 'settings'],
        ];
    }

    /**
     * @return array<int, array{label:string,icon:string,href:string}>
     */
    private function supportItems(): array
    {
        return [
            ['label' => 'Support', 'icon' => 'support', 'href' => '#'],
        ];
    }
}
