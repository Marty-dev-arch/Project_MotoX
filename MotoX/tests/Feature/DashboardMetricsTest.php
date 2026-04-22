<?php

namespace Tests\Feature;

use App\Models\Part;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesShopUser;
use Tests\TestCase;

class DashboardMetricsTest extends TestCase
{
    use CreatesShopUser;
    use RefreshDatabase;

    public function test_inventory_metrics_endpoint_returns_shop_scoped_realtime_data(): void
    {
        $user = $this->createShopUser('metrics@example.com', 'password123', 'Metrics Shop');
        $shop = $user->shop;

        $partA = Part::query()->create([
            'shop_id' => $shop->id,
            'name' => 'Chain',
            'sku' => 'DRV-001',
            'category' => 'Drivetrain',
            'minimum_stock' => 6,
            'unit_price' => 40,
            'is_active' => true,
        ]);

        $partB = Part::query()->create([
            'shop_id' => $shop->id,
            'name' => 'Oil Filter',
            'sku' => 'ENG-001',
            'category' => 'Engine',
            'minimum_stock' => 3,
            'unit_price' => 8,
            'is_active' => true,
        ]);

        StockMovement::query()->create([
            'part_id' => $partA->id,
            'user_id' => $user->id,
            'type' => 'in',
            'quantity' => 5,
            'reason' => 'Seed',
            'reference' => 'T1',
            'moved_at' => now()->subDay(),
        ]);

        StockMovement::query()->create([
            'part_id' => $partB->id,
            'user_id' => $user->id,
            'type' => 'in',
            'quantity' => 2,
            'reason' => 'Seed',
            'reference' => 'T2',
            'moved_at' => now(),
        ]);

        StockMovement::query()->create([
            'part_id' => $partB->id,
            'user_id' => $user->id,
            'type' => 'out',
            'quantity' => 2,
            'reason' => 'Issued',
            'reference' => 'T3',
            'moved_at' => now(),
        ]);

        $otherUser = $this->createShopUser('other-metrics@example.com', 'password123', 'Other Metrics Shop');
        $otherPart = Part::query()->create([
            'shop_id' => $otherUser->shop->id,
            'name' => 'Foreign Part',
            'sku' => 'OTH-001',
            'category' => 'Other',
            'minimum_stock' => 1,
            'unit_price' => 999,
            'is_active' => true,
        ]);
        StockMovement::query()->create([
            'part_id' => $otherPart->id,
            'user_id' => $otherUser->id,
            'type' => 'in',
            'quantity' => 10,
            'reason' => 'Other shop',
            'reference' => 'OT',
        ]);

        $response = $this->actingAs($user)->getJson('/dashboard/metrics/inventory');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'kpis' => ['total_skus', 'low_stock', 'out_of_stock', 'inventory_value'],
                'trend',
                'low_stock_by_category',
                'updated_at',
            ]);

        $json = $response->json();

        $this->assertSame('2', $json['kpis']['total_skus']);
        $this->assertSame('2', $json['kpis']['low_stock']);
        $this->assertSame('1', $json['kpis']['out_of_stock']);
        $this->assertCount(7, $json['trend']);
    }
}

