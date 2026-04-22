<?php

namespace Tests\Concerns;

use App\Models\Shop;
use App\Models\User;

trait CreatesShopUser
{
    protected function createShopUser(
        string $email = 'owner@example.com',
        string $password = 'password123',
        string $shopName = 'Test Garage'
    ): User {
        $user = User::factory()->create([
            'name' => 'Test Owner',
            'email' => $email,
            'password' => $password,
        ]);

        Shop::query()->create([
            'user_id' => $user->id,
            'name' => $shopName,
            'owner_name' => 'Test Owner',
            'contact_number' => '+63 900 000 0000',
        ]);

        return $user;
    }
}

