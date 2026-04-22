<?php

namespace App\Support;

use App\Models\Part;
use App\Models\Shop;
use App\Models\StockMovement;

class MotorcyclePartsCatalog
{
    /**
     * @return array<int, array{sku:string,name:string,category:string,minimum_stock:int,unit_price:float,initial_stock:int}>
     */
    public static function items(): array
    {
        return [
            ['sku' => 'TLS-001', 'name' => 'Toolkit', 'category' => 'Tools', 'minimum_stock' => 2, 'unit_price' => 45.00, 'initial_stock' => 6],
            ['sku' => 'ELE-001', 'name' => 'Battery', 'category' => 'Electrical', 'minimum_stock' => 4, 'unit_price' => 78.00, 'initial_stock' => 12],
            ['sku' => 'CTL-001', 'name' => 'Clutch Lever Set', 'category' => 'Controls', 'minimum_stock' => 5, 'unit_price' => 36.00, 'initial_stock' => 14],
            ['sku' => 'SAF-001', 'name' => 'Safety Bar', 'category' => 'Chassis', 'minimum_stock' => 3, 'unit_price' => 65.00, 'initial_stock' => 8],
            ['sku' => 'ELE-002', 'name' => 'Starter Pedal', 'category' => 'Electrical', 'minimum_stock' => 4, 'unit_price' => 22.00, 'initial_stock' => 11],
            ['sku' => 'BRK-001', 'name' => 'Brake Rod', 'category' => 'Brakes', 'minimum_stock' => 4, 'unit_price' => 18.00, 'initial_stock' => 10],
            ['sku' => 'EXH-001', 'name' => 'Muffler', 'category' => 'Exhaust', 'minimum_stock' => 2, 'unit_price' => 110.00, 'initial_stock' => 5],
            ['sku' => 'WHL-001', 'name' => 'Rim', 'category' => 'Wheels', 'minimum_stock' => 3, 'unit_price' => 92.00, 'initial_stock' => 9],
            ['sku' => 'DRV-001', 'name' => 'Chain', 'category' => 'Drivetrain', 'minimum_stock' => 6, 'unit_price' => 48.00, 'initial_stock' => 18],
            ['sku' => 'SUS-001', 'name' => 'Hydraulic Shocks', 'category' => 'Suspension', 'minimum_stock' => 3, 'unit_price' => 85.00, 'initial_stock' => 9],
            ['sku' => 'CTL-002', 'name' => 'Throttle Grip Set', 'category' => 'Controls', 'minimum_stock' => 5, 'unit_price' => 19.00, 'initial_stock' => 16],
            ['sku' => 'BDY-001', 'name' => 'Mirrors', 'category' => 'Body', 'minimum_stock' => 4, 'unit_price' => 27.00, 'initial_stock' => 12],
            ['sku' => 'ELE-003', 'name' => 'Headlight', 'category' => 'Electrical', 'minimum_stock' => 4, 'unit_price' => 58.00, 'initial_stock' => 13],
            ['sku' => 'ELE-004', 'name' => 'Horn', 'category' => 'Electrical', 'minimum_stock' => 6, 'unit_price' => 14.00, 'initial_stock' => 20],
            ['sku' => 'ELE-005', 'name' => 'Turn Signals (Pair)', 'category' => 'Electrical', 'minimum_stock' => 5, 'unit_price' => 25.00, 'initial_stock' => 14],
            ['sku' => 'WHL-002', 'name' => 'Rear Wheel', 'category' => 'Wheels', 'minimum_stock' => 2, 'unit_price' => 130.00, 'initial_stock' => 6],
            ['sku' => 'ELE-006', 'name' => 'Tail Light', 'category' => 'Electrical', 'minimum_stock' => 4, 'unit_price' => 32.00, 'initial_stock' => 12],
            ['sku' => 'FUE-001', 'name' => 'Fuel Cap', 'category' => 'Fuel System', 'minimum_stock' => 5, 'unit_price' => 15.00, 'initial_stock' => 16],
            ['sku' => 'BDY-002', 'name' => 'Seat', 'category' => 'Body', 'minimum_stock' => 3, 'unit_price' => 75.00, 'initial_stock' => 9],
            ['sku' => 'ELE-007', 'name' => 'Ignition Switch', 'category' => 'Electrical', 'minimum_stock' => 4, 'unit_price' => 29.00, 'initial_stock' => 10],
            ['sku' => 'ENG-001', 'name' => 'Oil Filter', 'category' => 'Engine', 'minimum_stock' => 12, 'unit_price' => 8.00, 'initial_stock' => 40],
            ['sku' => 'ENG-002', 'name' => 'Spark Plugs', 'category' => 'Engine', 'minimum_stock' => 10, 'unit_price' => 6.00, 'initial_stock' => 36],
            ['sku' => 'WHL-003', 'name' => 'Inner Tube', 'category' => 'Wheels', 'minimum_stock' => 8, 'unit_price' => 12.00, 'initial_stock' => 30],
            ['sku' => 'BRK-002', 'name' => 'Brake Cable', 'category' => 'Brakes', 'minimum_stock' => 7, 'unit_price' => 11.00, 'initial_stock' => 24],
            ['sku' => 'ELE-008', 'name' => 'Starter Motor', 'category' => 'Electrical', 'minimum_stock' => 3, 'unit_price' => 95.00, 'initial_stock' => 8],
            ['sku' => 'CHS-001', 'name' => 'Frame', 'category' => 'Chassis', 'minimum_stock' => 1, 'unit_price' => 240.00, 'initial_stock' => 3],
            ['sku' => 'FUE-002', 'name' => 'Fuel Tank', 'category' => 'Fuel System', 'minimum_stock' => 2, 'unit_price' => 165.00, 'initial_stock' => 5],
            ['sku' => 'STR-001', 'name' => 'Triple Tree', 'category' => 'Steering', 'minimum_stock' => 2, 'unit_price' => 125.00, 'initial_stock' => 6],
            ['sku' => 'STR-002', 'name' => 'Handlebars', 'category' => 'Steering', 'minimum_stock' => 3, 'unit_price' => 68.00, 'initial_stock' => 10],
            ['sku' => 'CTL-003', 'name' => 'Grips', 'category' => 'Controls', 'minimum_stock' => 8, 'unit_price' => 10.00, 'initial_stock' => 26],
            ['sku' => 'INS-001', 'name' => 'Speedometer', 'category' => 'Instrumentation', 'minimum_stock' => 3, 'unit_price' => 54.00, 'initial_stock' => 9],
            ['sku' => 'ELE-009', 'name' => 'Key Set', 'category' => 'Electrical', 'minimum_stock' => 6, 'unit_price' => 13.00, 'initial_stock' => 18],
            ['sku' => 'INS-002', 'name' => 'Tachometer', 'category' => 'Instrumentation', 'minimum_stock' => 3, 'unit_price' => 52.00, 'initial_stock' => 8],
            ['sku' => 'INS-003', 'name' => 'Fuel Gauge', 'category' => 'Instrumentation', 'minimum_stock' => 4, 'unit_price' => 24.00, 'initial_stock' => 11],
            ['sku' => 'SUS-002', 'name' => 'Forks', 'category' => 'Suspension', 'minimum_stock' => 2, 'unit_price' => 140.00, 'initial_stock' => 5],
            ['sku' => 'BRK-003', 'name' => 'Rear Brake Assembly', 'category' => 'Brakes', 'minimum_stock' => 3, 'unit_price' => 78.00, 'initial_stock' => 9],
            ['sku' => 'ENG-003', 'name' => 'Engine', 'category' => 'Engine', 'minimum_stock' => 1, 'unit_price' => 450.00, 'initial_stock' => 2],
            ['sku' => 'DRV-002', 'name' => 'Sprockets', 'category' => 'Drivetrain', 'minimum_stock' => 5, 'unit_price' => 28.00, 'initial_stock' => 15],
            ['sku' => 'BDY-003', 'name' => 'Front Fender', 'category' => 'Body', 'minimum_stock' => 3, 'unit_price' => 44.00, 'initial_stock' => 10],
            ['sku' => 'ENG-004', 'name' => 'Oil Cooler', 'category' => 'Engine', 'minimum_stock' => 2, 'unit_price' => 102.00, 'initial_stock' => 6],
            ['sku' => 'BRK-004', 'name' => 'Front Brake Assembly', 'category' => 'Brakes', 'minimum_stock' => 3, 'unit_price' => 86.00, 'initial_stock' => 9],
            ['sku' => 'ELE-010', 'name' => 'Stator', 'category' => 'Electrical', 'minimum_stock' => 3, 'unit_price' => 72.00, 'initial_stock' => 9],
            ['sku' => 'DRV-003', 'name' => 'Gear Shift Lever', 'category' => 'Drivetrain', 'minimum_stock' => 4, 'unit_price' => 20.00, 'initial_stock' => 12],
            ['sku' => 'CHS-002', 'name' => 'Kickstand', 'category' => 'Chassis', 'minimum_stock' => 3, 'unit_price' => 26.00, 'initial_stock' => 10],
            ['sku' => 'CHS-003', 'name' => 'Foot Pegs', 'category' => 'Chassis', 'minimum_stock' => 6, 'unit_price' => 16.00, 'initial_stock' => 20],
        ];
    }

    public static function seedShop(Shop $shop, ?int $userId = null): void
    {
        foreach (self::items() as $item) {
            $part = Part::query()->updateOrCreate(
                [
                    'shop_id' => $shop->id,
                    'sku' => $item['sku'],
                ],
                [
                    'name' => $item['name'],
                    'category' => $item['category'],
                    'minimum_stock' => $item['minimum_stock'],
                    'unit_price' => $item['unit_price'],
                    'is_active' => true,
                ]
            );

            if (! $part->movements()->exists()) {
                StockMovement::query()->create([
                    'part_id' => $part->id,
                    'user_id' => $userId,
                    'type' => StockMovement::TYPE_IN,
                    'quantity' => $item['initial_stock'],
                    'reason' => 'Initial seeded stock',
                    'reference' => 'SEED',
                    'moved_at' => now(),
                ]);
            }
        }
    }
}

