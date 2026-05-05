<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\User;
use App\Support\MotorcyclePartsCatalog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'MotoX Demo Admin',
            'email' => 'admin@motox.test',
            'password' => 'password123',
            'role' => 'admin',
        ]);

        $shop = Shop::query()->create([
            'user_id' => $user->id,
            'name' => 'MotoX Main Garage',
            'owner_name' => 'MotoX Demo Admin',
            'contact_number' => '+63 900 000 0000',
        ]);

        $user->update(['shop_id' => $shop->id]);

        MotorcyclePartsCatalog::seedShop($shop, $user->id);
    }
}
