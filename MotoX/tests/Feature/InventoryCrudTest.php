<?php

namespace Tests\Feature;

use App\Models\Part;
use App\Models\StockMovement;
use App\Support\InventoryMetrics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesShopUser;
use Tests\TestCase;

class InventoryCrudTest extends TestCase
{
    use CreatesShopUser;
    use RefreshDatabase;

    public function test_user_can_create_update_and_delete_part_without_movements(): void
    {
        $user = $this->createShopUser();
        $shop = $user->shop;

        $this->actingAs($user);

        $this->post('/inventory/parts', [
            'name' => 'Chain Kit',
            'sku' => 'DRV-999',
            'category' => 'Drivetrain',
            'minimum_stock' => 4,
            'unit_price' => 55.5,
            'is_active' => 1,
        ])->assertRedirect('/inventory');

        $part = Part::query()->where('shop_id', $shop->id)->where('sku', 'DRV-999')->firstOrFail();

        $this->put("/inventory/parts/{$part->id}", [
            'name' => 'Premium Chain Kit',
            'sku' => 'DRV-999',
            'category' => 'Drivetrain',
            'minimum_stock' => 5,
            'unit_price' => 60.75,
            'is_active' => 1,
        ])->assertRedirect('/inventory');

        $this->assertDatabaseHas('parts', [
            'id' => $part->id,
            'name' => 'Premium Chain Kit',
            'minimum_stock' => 5,
        ]);

        $this->delete("/inventory/parts/{$part->id}")
            ->assertRedirect('/inventory');

        $this->assertDatabaseMissing('parts', [
            'id' => $part->id,
        ]);
    }

    public function test_stock_rules_prevent_negative_balance_and_delete_with_history(): void
    {
        $user = $this->createShopUser();
        $shop = $user->shop;
        $this->actingAs($user);

        $part = Part::query()->create([
            'shop_id' => $shop->id,
            'name' => 'Brake Cable',
            'sku' => 'BRK-998',
            'category' => 'Brakes',
            'minimum_stock' => 3,
            'unit_price' => 14,
            'is_active' => true,
        ]);

        StockMovement::query()->create([
            'part_id' => $part->id,
            'user_id' => $user->id,
            'type' => StockMovement::TYPE_IN,
            'quantity' => 5,
            'reason' => 'Initial stock',
            'reference' => 'TEST',
        ]);

        $this->post("/inventory/parts/{$part->id}/movements", [
            'type' => 'out',
            'quantity' => 10,
            'reason' => 'Over issue',
        ])->assertSessionHasErrors('quantity');

        $this->post("/inventory/parts/{$part->id}/movements", [
            'type' => 'out',
            'quantity' => 3,
            'reason' => 'Service usage',
        ])->assertRedirect('/inventory');

        $currentStock = InventoryMetrics::partsWithStockQuery($shop->id)
            ->where('parts.id', $part->id)
            ->value('current_stock');

        $this->assertSame(2, (int) $currentStock);

        $this->delete("/inventory/parts/{$part->id}")
            ->assertSessionHasErrors('inventory');
    }

    public function test_user_cannot_modify_part_owned_by_another_shop(): void
    {
        $owner = $this->createShopUser('owner@example.com', 'password123', 'Owner Shop');
        $other = $this->createShopUser('other@example.com', 'password123', 'Other Shop');

        $part = Part::query()->create([
            'shop_id' => $owner->shop->id,
            'name' => 'Fuel Cap',
            'sku' => 'FUE-998',
            'category' => 'Fuel System',
            'minimum_stock' => 2,
            'unit_price' => 11.0,
            'is_active' => true,
        ]);

        $this->actingAs($other);

        $this->put("/inventory/parts/{$part->id}", [
            'name' => 'Tampered Fuel Cap',
            'sku' => 'FUE-998',
            'category' => 'Fuel System',
            'minimum_stock' => 2,
            'unit_price' => 11,
            'is_active' => 1,
        ])->assertNotFound();

        $this->post("/inventory/parts/{$part->id}/movements", [
            'type' => 'in',
            'quantity' => 2,
        ])->assertNotFound();
    }
}

