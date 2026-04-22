<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesShopUser;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use CreatesShopUser;
    use RefreshDatabase;

    /**
     * Ensure the frontend showcase pages render successfully.
     */
    public function test_the_frontend_pages_render_successfully(): void
    {
        foreach ([
            '/' => 'Grow your',
            '/login' => 'Welcome back',
            '/register' => 'Create your account',
        ] as $uri => $expected) {
            $this->get($uri)
                ->assertOk()
                ->assertSeeText($expected);
        }
    }

    public function test_protected_pages_render_when_authenticated(): void
    {
        $user = $this->createShopUser();

        $this->actingAs($user);

        foreach ([
            '/dashboard' => 'Dashboard',
            '/inventory' => 'Spare Parts Ledger',
            '/customers' => 'Customers',
            '/job-orders' => 'Job Orders',
            '/billing' => 'Billing',
            '/reports' => 'Reports',
            '/settings' => 'Settings',
        ] as $uri => $expected) {
            $this->get($uri)
                ->assertOk()
                ->assertSeeText($expected);
        }
    }
}
