<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesShopUser;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use CreatesShopUser;
    use RefreshDatabase;

    public function test_guest_is_redirected_from_protected_routes(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/inventory')->assertRedirect('/login');
    }

    public function test_user_can_register_with_shop_without_seeding_demo_parts(): void
    {
        $response = $this->post('/register', [
            'shop_name' => 'Loylo Motors',
            'owner_name' => 'Loylo Owner',
            'email' => 'loylo@example.com',
            'contact_number' => '+63 912 345 6789',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('shops', [
            'name' => 'Loylo Motors',
            'owner_name' => 'Loylo Owner',
        ]);
        $this->assertDatabaseCount('parts', 0);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = $this->createShopUser('auth@example.com', 'password123');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }
}
