<?php

namespace App\Support;

class WorkshopDemo
{
    public static function user(): array
    {
        return [
            'name' => 'Jhon Llyod',
            'role' => 'Shop Manager',
            'initials' => 'JL',
            'online' => true,
        ];
    }

    public static function navigation(): array
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

    public static function supportLinks(): array
    {
        return [
            ['label' => 'Support', 'icon' => 'support', 'href' => '#'],
        ];
    }

    public static function dashboard(): array
    {
        return [
'heading' => 'Dashboard',
            'subheading' => "Workshop summary and key metrics.",
            'searchPlaceholder' => 'Search orders, parts, customers...',
            'stats' => [
                ['label' => 'Active Job Orders', 'value' => '24', 'caption' => 'Currently in queue', 'icon' => 'wrench', 'trend' => '+12%', 'trendTone' => 'success'],
                ['label' => 'Daily Revenue', 'value' => 'PHP 4,250', 'caption' => 'Booked today', 'icon' => 'billing', 'trend' => '+5.2%', 'trendTone' => 'success'],
                ['label' => 'Low-Stock Alerts', 'value' => '8', 'caption' => 'Action needed', 'icon' => 'alert', 'trend' => 'Action Needed', 'trendTone' => 'danger'],
                ['label' => 'Pending Invoices', 'value' => '12', 'caption' => 'Awaiting payment', 'icon' => 'file'],
            ],
            'recentOrders' => [
                ['vehicle' => 'Ducati Panigale V4', 'customer' => 'Marcus Johnson', 'job' => '#JO-4921', 'service' => 'Desmo Service', 'amount' => 'PHP 1,250', 'status' => 'In Progress', 'statusTone' => 'success', 'thumbTone' => 'from-red-500 to-orange-500'],
                ['vehicle' => 'BMW R1250GS', 'customer' => "Sarah O'Connor", 'job' => '#JO-4920', 'service' => 'Tire Mount & Balance', 'amount' => 'PHP 320', 'status' => 'Waiting Parts', 'statusTone' => 'warning', 'thumbTone' => 'from-slate-700 to-slate-500'],
                ['vehicle' => 'Yamaha MT-09', 'customer' => 'David Chen', 'job' => '#JO-4919', 'service' => 'Suspension Tuning', 'amount' => 'PHP 450', 'status' => 'Scheduled', 'statusTone' => 'neutral', 'thumbTone' => 'from-cyan-500 to-sky-500'],
            ],
            'inventoryAlerts' => [
                ['name' => 'Motul 7100 10W-40', 'code' => 'PT-OIL-001', 'detail' => 'Synthetic Oil', 'badge' => 'Critical', 'badgeTone' => 'danger', 'minimum' => '12', 'qty' => '2'],
                ['name' => 'Brembo P04 Brake Pads', 'code' => 'PT-BRK-092', 'detail' => 'Caliper Inserts', 'badge' => 'Low', 'badgeTone' => 'warning', 'minimum' => '5', 'qty' => '1'],
                ['name' => 'Pirelli Diablo Supercorsa SP', 'code' => 'PT-TIR-180', 'detail' => 'Rear 180/55 ZR17', 'badge' => 'Low', 'badgeTone' => 'warning', 'minimum' => '4', 'qty' => '2'],
            ],
            'moduleHighlights' => [
                ['title' => 'Customer & Vehicle Records', 'detail' => 'Centralized contacts, registered vehicles, and complete service history per unit.'],
                ['title' => 'Inventory Control', 'detail' => 'Live stock counts, movement logs, reorder thresholds, and low-stock alerts.'],
                ['title' => 'Repair Workflow', 'detail' => 'Track each job from intake to diagnostics, repair, QA, and release.'],
                ['title' => 'Billing Automation', 'detail' => 'Labor and parts are rolled up into invoice totals and tax-ready summaries.'],
            ],
        ];
    }

    public static function customers(): array
    {
        return [
            'heading' => 'Customers',
            'searchPlaceholder' => 'Search name, plate, ID...',
            'customers' => [
                ['name' => 'Elias Vance', 'phone' => '(555) 019-8234', 'email' => 'elias.vance@example.com', 'id' => 'C-882', 'vehicles' => '2 Vehicles', 'status' => 'Active Order', 'statusTone' => 'success', 'active' => true],
                ['name' => 'Sarah Jenkins', 'phone' => '(555) 234-9111', 'email' => 'sarah.jenkins@example.com', 'id' => 'C-881', 'vehicles' => '1 Vehicle', 'status' => 'Needs Follow-Up', 'statusTone' => 'warning', 'active' => false],
            ],
            'selectedCustomer' => [
                'name' => 'Elias Vance',
                'id' => 'C-882',
                'summary' => 'Customer since March 2026. Preferred contact method: SMS. Standard hourly rate applies.',
                'contact' => [
                    ['label' => 'Primary Phone', 'value' => '(555) 019-8234'],
                    ['label' => 'Email Address', 'value' => 'elias.vance@example.com'],
                    ['label' => 'Address', 'value' => '125 Redwood Ave, Los Angeles, CA'],
                    ['label' => 'Preferred Contact', 'value' => 'SMS between 8AM and 5PM'],
                ],
                'serviceHistory' => [
                    ['date' => 'Apr 18, 2026', 'vehicle' => '1969 Mustang Boss 302', 'job' => 'JO-4092', 'service' => 'Tune-up and inspection', 'status' => 'In Progress', 'tone' => 'accent'],
                    ['date' => 'Apr 16, 2026', 'vehicle' => '2018 Tacoma TRD Pro', 'job' => 'JO-4090', 'service' => 'Oil and filter service', 'status' => 'Completed', 'tone' => 'success'],
                    ['date' => 'Feb 04, 2026', 'vehicle' => '2018 Tacoma TRD Pro', 'job' => 'JO-4012', 'service' => 'Brake flush and pad replacement', 'status' => 'Completed', 'tone' => 'success'],
                    ['date' => 'Nov 12, 2025', 'vehicle' => '1969 Mustang Boss 302', 'job' => 'JO-3894', 'service' => 'Electrical diagnostics', 'status' => 'Closed', 'tone' => 'neutral'],
                ],
                'vehicles' => [
                    [
                        'title' => '1969 Ford Mustang Boss 302',
                        'vin' => 'VIN-8F02G123456',
                        'plate' => 'BOS-302',
                        'engine' => '302 cu in (4.9 L) V8',
                        'odometer' => '42,850 mi',
                        'metaLabel' => 'Active Job',
                        'metaValue' => '#JO-4092',
                        'metaTone' => 'accent',
                        'history' => [
                            ['date' => 'Apr 18, 2026', 'service' => 'Spark plug replacement and timing inspection', 'advisor' => 'A. Rivera'],
                            ['date' => 'Jan 22, 2026', 'service' => 'Cooling system flush', 'advisor' => 'M. Santos'],
                        ],
                    ],
                    [
                        'title' => '2018 Toyota Tacoma TRD Pro',
                        'vin' => 'VIN-3TMCZ5AN1J',
                        'plate' => 'MTN-RNR',
                        'engine' => '3.5L V6',
                        'odometer' => '68,120 mi',
                        'metaLabel' => 'History',
                        'metaValue' => '4 Past Jobs',
                        'metaTone' => 'neutral',
                        'history' => [
                            ['date' => 'Apr 16, 2026', 'service' => 'Oil and filter service', 'advisor' => 'A. Rivera'],
                            ['date' => 'Feb 04, 2026', 'service' => 'Brake service and tire rotation', 'advisor' => 'D. Cruz'],
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function jobOrders(): array
    {
        $services = [
            ['description' => 'Spark plug replacement (x8)', 'technician' => 'A. Rivera', 'hours' => 1.5, 'rate' => 90, 'status' => 'Done', 'tone' => 'success'],
            ['description' => 'Air filter replacement', 'technician' => 'A. Rivera', 'hours' => 0.5, 'rate' => 90, 'status' => 'Done', 'tone' => 'success'],
            ['description' => 'Timing belt inspection', 'technician' => 'A. Rivera', 'hours' => 2.0, 'rate' => 90, 'status' => 'In Progress', 'tone' => 'accent'],
            ['description' => 'Fuel injector cleaning', 'technician' => 'A. Rivera', 'hours' => 0.5, 'rate' => 90, 'status' => 'Pending', 'tone' => 'neutral'],
        ];

        $parts = [
            ['name' => 'NGK Iridium Spark Plug', 'sku' => 'NGK-BKR6EIX', 'qty' => 8, 'price' => 5],
            ['name' => 'K&N Performance Air Filter', 'sku' => 'KN-E-0940', 'qty' => 1, 'price' => 62],
        ];

        $laborTotal = array_sum(array_map(fn (array $service): float => $service['hours'] * $service['rate'], $services));
        $partsTotal = array_sum(array_map(fn (array $part): float => $part['qty'] * $part['price'], $parts));

        return [
            'heading' => 'Job Orders',
            'orders' => [
                ['number' => 'JO-4092', 'customer' => 'Elias Vance', 'vehicle' => '1969 Mustang Boss 302', 'status' => 'In Progress', 'tone' => 'accent', 'active' => true],
                ['number' => 'JO-4091', 'customer' => 'Marco Reyes', 'vehicle' => '2021 Tacoma SR5', 'status' => 'Awaiting Parts', 'tone' => 'warning'],
                ['number' => 'JO-4090', 'customer' => 'Elias Vance', 'vehicle' => '2018 Tacoma TRD Pro', 'status' => 'Completed', 'tone' => 'success'],
                ['number' => 'JO-4089', 'customer' => 'Sarah Jenkins', 'vehicle' => '2022 Toyota RAV4', 'status' => 'In Progress', 'tone' => 'accent'],
                ['number' => 'JO-4088', 'customer' => 'Marco Reyes', 'vehicle' => '2016 Ford F-150', 'status' => 'Completed', 'tone' => 'success'],
            ],
            'selectedOrder' => [
                'number' => 'JO-4092',
                'status' => 'In Progress',
                'statusTone' => 'accent',
                'priority' => 'High Priority',
                'priorityTone' => 'danger',
                'meta' => 'Created Apr 18, 2026 | Assigned to A. Rivera | Est. completion: Apr 20, 2026',
                'progress' => 68,
                'stages' => [
                    ['label' => 'Intake', 'done' => true],
                    ['label' => 'Diagnostics', 'done' => true],
                    ['label' => 'Repair', 'done' => true],
                    ['label' => 'Quality Check', 'done' => false, 'active' => true],
                    ['label' => 'Release', 'done' => false],
                ],
                'customer' => ['label' => 'Customer', 'name' => 'Elias Vance', 'detail' => 'ID: C-882'],
                'vehicle' => ['label' => 'Vehicle', 'name' => '1969 Mustang Boss 302', 'detail' => 'Plate: BOS-302'],
                'cost' => ['label' => 'Estimated Cost', 'name' => self::money($laborTotal + $partsTotal), 'detail' => 'Labor + Parts'],
                'intake' => [
                    ['label' => 'Concern', 'value' => 'Engine misfire and overdue tune-up'],
                    ['label' => 'Mileage In', 'value' => '42,850 mi'],
                    ['label' => 'Promised Date', 'value' => 'Apr 20, 2026'],
                    ['label' => 'Advisor', 'value' => 'A. Rivera'],
                ],
                'timeline' => [
                    ['time' => '08:15', 'title' => 'Vehicle checked in', 'detail' => 'Initial intake completed and customer approved inspection.'],
                    ['time' => '09:40', 'title' => 'Diagnostics finished', 'detail' => 'Technician confirmed worn plugs and dirty air filter.'],
                    ['time' => '12:10', 'title' => 'Repair started', 'detail' => 'Parts allocated from ignition and filters inventory bins.'],
                    ['time' => '15:05', 'title' => 'Awaiting final QA', 'detail' => 'Road test and invoice review pending.'],
                ],
                'services' => array_map(fn (array $service): array => array_merge($service, [
                    'rateDisplay' => self::money($service['rate']).'/hr',
                    'hoursDisplay' => number_format($service['hours'], 1),
                    'total' => self::money($service['hours'] * $service['rate']),
                ]), $services),
                'parts' => array_map(fn (array $part): array => array_merge($part, [
                    'priceDisplay' => self::money($part['price']),
                    'total' => self::money($part['qty'] * $part['price']),
                ]), $parts),
                'summary' => [
                    ['label' => 'Labor Cost', 'value' => self::money($laborTotal)],
                    ['label' => 'Parts Cost', 'value' => self::money($partsTotal)],
                    ['label' => 'Projected Invoice', 'value' => self::money($laborTotal + $partsTotal), 'emphasized' => true],
                ],
            ],
        ];
    }

    public static function inventory(): array
    {
        return [
'heading' => 'Inventory',
            'meta' => 'Last updated: Apr 20, 2026 | 6 categories | 138 total units in stock',
            'categories' => [
                ['name' => 'All Parts', 'count' => '6 items', 'active' => true],
                ['name' => 'Brakes', 'count' => '1 item'],
                ['name' => 'Lubricants', 'count' => '1 item'],
                ['name' => 'Filters', 'count' => '2 items'],
                ['name' => 'Ignition', 'count' => '1 item'],
                ['name' => 'Engine', 'count' => '1 item'],
            ],
            'stats' => [
                ['label' => 'Total SKUs', 'value' => '142', 'caption' => 'Active parts', 'icon' => 'inventory'],
                ['label' => 'Low Stock', 'value' => '8', 'caption' => 'Below minimum', 'icon' => 'alert', 'tone' => 'warning'],
                ['label' => 'Out of Stock', 'value' => '3', 'caption' => 'Needs reorder', 'icon' => 'alert', 'tone' => 'danger'],
                ['label' => 'Est. Value', 'value' => '$12,440', 'caption' => 'On hand', 'icon' => 'billing'],
            ],
            'alerts' => [
                ['part' => 'Engine Oil 5W-30 (5L)', 'current' => 6, 'minimum' => 8, 'severity' => 'Low Stock', 'tone' => 'warning'],
                ['part' => 'Air Filter Universal', 'current' => 0, 'minimum' => 5, 'severity' => 'Out of Stock', 'tone' => 'danger'],
                ['part' => 'Timing Belt Kit', 'current' => 3, 'minimum' => 4, 'severity' => 'Low Stock', 'tone' => 'warning'],
            ],
            'movements' => [
                ['time' => '4:18 PM', 'part' => 'NGK Spark Plug Iridium', 'action' => 'Allocated to JO-4092', 'qty' => '-8'],
                ['time' => '2:03 PM', 'part' => 'Cabin Air Filter', 'action' => 'Received from supplier', 'qty' => '+12'],
                ['time' => '11:47 AM', 'part' => 'Engine Oil 5W-30 (5L)', 'action' => 'Consumed on INV-1041', 'qty' => '-1'],
            ],
            'parts' => [
                ['name' => 'Brake Pads Front Set', 'sku' => 'BP-F-2201', 'category' => 'Brakes', 'stock' => 24, 'minimum' => 10, 'price' => '$38.00', 'status' => 'In Stock', 'tone' => 'success'],
                ['name' => 'Engine Oil 5W-30 (5L)', 'sku' => 'OIL-5W30-5L', 'category' => 'Lubricants', 'stock' => 6, 'minimum' => 8, 'price' => '$22.00', 'status' => 'Low Stock', 'tone' => 'warning'],
                ['name' => 'Air Filter Universal', 'sku' => 'AF-UNV-44', 'category' => 'Filters', 'stock' => 0, 'minimum' => 5, 'price' => '$18.00', 'status' => 'Out of Stock', 'tone' => 'danger'],
                ['name' => 'NGK Spark Plug Iridium', 'sku' => 'NGK-BKR6EIX', 'category' => 'Ignition', 'stock' => 88, 'minimum' => 20, 'price' => '$5.00', 'status' => 'In Stock', 'tone' => 'success'],
                ['name' => 'Timing Belt Kit', 'sku' => 'TBK-H-2218', 'category' => 'Engine', 'stock' => 3, 'minimum' => 4, 'price' => '$110.00', 'status' => 'Low Stock', 'tone' => 'warning'],
                ['name' => 'Cabin Air Filter', 'sku' => 'CAF-T-4401', 'category' => 'Filters', 'stock' => 17, 'minimum' => 5, 'price' => '$14.00', 'status' => 'In Stock', 'tone' => 'success'],
            ],
        ];
    }

    public static function billing(): array
    {
        $laborItems = [
            ['description' => 'Oil & Filter Service - 2018 Tacoma TRD Pro', 'qty' => 1, 'unit_price' => 55],
            ['description' => 'Brake inspection labor', 'qty' => 1, 'unit_price' => 22],
        ];

        $partsItems = [
            ['description' => 'Synthetic Motor Oil 5W-30 (5L)', 'qty' => 1, 'unit_price' => 22],
            ['description' => 'Oil Filter Cartridge', 'qty' => 1, 'unit_price' => 18],
        ];

        $allItems = array_merge(
            array_map(fn (array $item): array => array_merge($item, ['type' => 'Labor']), $laborItems),
            array_map(fn (array $item): array => array_merge($item, ['type' => 'Parts']), $partsItems),
        );

        $subtotal = array_sum(array_map(fn (array $item): float => $item['qty'] * $item['unit_price'], $allItems));
        $tax = round($subtotal * 0.10, 2);
        $total = $subtotal + $tax;
        $laborTotal = array_sum(array_map(fn (array $item): float => $item['qty'] * $item['unit_price'], $laborItems));
        $partsTotal = array_sum(array_map(fn (array $item): float => $item['qty'] * $item['unit_price'], $partsItems));

        return [
            'heading' => 'Billing',
            'invoices' => [
                ['number' => 'INV-1041', 'customer' => 'Elias Vance', 'amount' => self::money($total), 'date' => 'Apr 16', 'status' => 'Paid', 'tone' => 'success', 'active' => true],
                ['number' => 'INV-1040', 'customer' => 'Marco Reyes', 'amount' => '$560.00', 'date' => 'Apr 14', 'status' => 'Paid', 'tone' => 'success'],
                ['number' => 'INV-1039', 'customer' => 'Sarah Jenkins', 'amount' => '$230.00', 'date' => 'Apr 10', 'status' => 'Overdue', 'tone' => 'danger'],
                ['number' => 'INV-1038', 'customer' => 'Diana Cho', 'amount' => '$418.00', 'date' => 'Apr 8', 'status' => 'Paid', 'tone' => 'success'],
                ['number' => 'INV-1037', 'customer' => 'Marco Reyes', 'amount' => '$95.00', 'date' => 'Apr 5', 'status' => 'Pending', 'tone' => 'warning'],
            ],
            'selectedInvoice' => [
                'number' => 'INV-1041',
                'status' => 'Paid',
                'tone' => 'success',
                'meta' => 'Issued Apr 16, 2026 | Paid Apr 16, 2026 | Linked to JO-4090',
                'summary' => [
                    ['label' => 'Invoice Total', 'value' => self::money($total), 'caption' => 'Auto-computed'],
                    ['label' => 'Labor Charges', 'value' => self::money($laborTotal), 'caption' => 'From service lines'],
                    ['label' => 'Parts Charges', 'value' => self::money($partsTotal), 'caption' => 'From inventory usage'],
                ],
                'billTo' => ['name' => 'Elias Vance', 'phone' => '(555) 019-8234', 'contact' => 'Preferred: SMS | ID: C-882'],
                'payment' => ['method' => 'Visa **** 4821', 'date' => 'Apr 16, 2026'],
                'items' => array_map(fn (array $item): array => [
                    'description' => $item['description'],
                    'type' => $item['type'],
                    'qty' => (string) $item['qty'],
                    'price' => self::money($item['unit_price']),
                    'total' => self::money($item['qty'] * $item['unit_price']),
                ], $allItems),
                'totals' => [
                    ['label' => 'Subtotal', 'value' => self::money($subtotal)],
                    ['label' => 'Tax (10%)', 'value' => self::money($tax)],
                    ['label' => 'Total', 'value' => self::money($total), 'emphasized' => true],
                ],
            ],
        ];
    }

    public static function reports(): array
    {
        return [
'heading' => 'Reports',
            'period' => 'Apr 2026',
            'meta' => '7-month overview | Updated: Apr 20, 2026 | All technicians',
            'sections' => [
                ['name' => 'Revenue', 'active' => true],
                ['name' => 'Job Orders'],
                ['name' => 'Technicians'],
                ['name' => 'Parts Usage'],
                ['name' => 'Customer Trends'],
            ],
            'stats' => [
                ['label' => 'Apr Revenue', 'value' => '$7,100', 'caption' => 'Month to date', 'icon' => 'billing'],
                ['label' => 'Vs Last Month', 'value' => '+15.4%', 'caption' => 'Up from Mar', 'icon' => 'trend'],
                ['label' => 'Avg Job Value', 'value' => '$284', 'caption' => 'Per closed order', 'icon' => 'file'],
                ['label' => 'Jobs Closed', 'value' => '25', 'caption' => 'This month', 'icon' => 'job-orders'],
            ],
            'dailySales' => [
                ['day' => 'Mon', 'orders' => 6, 'sales' => '$1,240'],
                ['day' => 'Tue', 'orders' => 7, 'sales' => '$1,540'],
                ['day' => 'Wed', 'orders' => 5, 'sales' => '$990'],
                ['day' => 'Thu', 'orders' => 4, 'sales' => '$810'],
                ['day' => 'Fri', 'orders' => 8, 'sales' => '$1,820'],
            ],
            'inventoryStatus' => [
                ['label' => 'Healthy SKUs', 'value' => '131', 'tone' => 'success'],
                ['label' => 'Low Stock', 'value' => '8', 'tone' => 'warning'],
                ['label' => 'Out of Stock', 'value' => '3', 'tone' => 'danger'],
            ],
            'serviceAnalytics' => [
                ['service' => 'Oil & Routine Service', 'jobs' => 14, 'avg_time' => '1.4 hr', 'conversion' => '92%'],
                ['service' => 'Brake & Suspension', 'jobs' => 9, 'avg_time' => '2.3 hr', 'conversion' => '88%'],
                ['service' => 'Diagnostics', 'jobs' => 7, 'avg_time' => '1.8 hr', 'conversion' => '76%'],
            ],
            'monthly' => [
                ['label' => 'Oct', 'value' => 58],
                ['label' => 'Nov', 'value' => 72],
                ['label' => 'Dec', 'value' => 54],
                ['label' => 'Jan', 'value' => 84],
                ['label' => 'Feb', 'value' => 78],
                ['label' => 'Mar', 'value' => 98],
                ['label' => 'Apr', 'value' => 82, 'current' => true],
            ],
            'categories' => [
                ['name' => 'Engine & Drivetrain', 'jobs' => '8', 'revenue' => '$3,120', 'share' => 44, 'change' => '+8%', 'changeTone' => 'success', 'barTone' => 'accent'],
                ['name' => 'Brakes & Suspension', 'jobs' => '7', 'revenue' => '$1,890', 'share' => 27, 'change' => '+3%', 'changeTone' => 'success', 'barTone' => 'indigo'],
                ['name' => 'Oil & Routine Service', 'jobs' => '6', 'revenue' => '$980', 'share' => 14, 'change' => '-2%', 'changeTone' => 'danger', 'barTone' => 'teal'],
                ['name' => 'AC & Electrical', 'jobs' => '4', 'revenue' => '$1,110', 'share' => 15, 'change' => '+12%', 'changeTone' => 'success', 'barTone' => 'amber'],
            ],
        ];
    }

    public static function settings(): array
    {
        return [
            'heading' => 'Settings',
            'subheading' => 'Manage your workspace preferences and profile configuration.',
            'tabs' => [
                ['name' => 'Profile Settings', 'active' => true],
                ['name' => 'Shop Preferences'],
                ['name' => 'Appearance'],
            ],
            'profile' => [
                'displayName' => 'Jhon Llyod',
                'role' => 'Mechanic',
                'firstName' => 'David',
                'lastName' => 'Reynolds',
                'email' => 'david@mechanicalatelier.com',
            ],
            'preferences' => [
                'laborRate' => '145',
                'currencies' => [
                    ['code' => 'USD ($)', 'active' => true],
                    ['code' => 'PHP (P)', 'active' => false],
                    ['code' => 'GBP (L)', 'active' => false],
                ],
                'autoAssign' => true,
            ],
            'appearance' => [
                'active' => 'Light Mode',
                'modes' => [
                    ['name' => 'Light Mode', 'icon' => 'sun', 'active' => true],
                    ['name' => 'Dark Mode', 'icon' => 'moon', 'active' => false],
                ],
            ],
        ];
    }

    public static function landing(): array
    {
        return [
            'eyebrow' => 'Automotive Management',
            'titleLead' => 'Grow your',
            'titleAccent' => "workshop's",
            'titleTail' => 'potential.',
            'description' => 'Move beyond spreadsheets. MotoX delivers clean inventory, work orders, and client records to modernize the garage.',
            'activeOrders' => [
                ['vehicle' => 'Porsche 911 Carrera', 'service' => 'Engine Diagnostics', 'amount' => 'P2586', 'status' => 'In Progress', 'statusTone' => 'success'],
                ['vehicle' => 'BMW M3', 'service' => 'Suspension Tune', 'amount' => 'P778', 'status' => 'Scheduled', 'statusTone' => 'neutral'],
            ],
            'efficiency' => '+34%',
            'modules' => [
                [
                    'title' => 'The Part Ledger',
                    'detail' => 'Stop guessing on margins. Track pricing, quantity, and movement with clear inventory records.',
                    'link' => 'Explore Inventory',
                    'route' => 'inventory',
                    'icon' => 'inventory',
                ],
                [
                    'title' => 'Editorial Work Orders',
                    'detail' => 'Convert repair tickets into clean digital documents so technicians always see key details first.',
                    'link' => 'View Work Orders',
                    'route' => 'job-orders',
                    'icon' => 'wrench',
                ],
                [
                    'title' => 'Client Transparency',
                    'detail' => 'Generate invoices and service reports clients can understand, then send updates instantly.',
                    'link' => 'See CRM Tools',
                    'route' => 'customers',
                    'icon' => 'customers',
                ],
            ],
            'missionPoints' => [
                'Designed for high-density clarity',
                'Eliminating operational friction',
                'Elevating the customer experience',
            ],
        ];
    }

    public static function login(): array
    {
        return [
            'brand' => 'MotoX',
            'welcome' => 'Hello, Welcome Back',
            'roles' => ['Mechanic', 'Shop Manager', 'Cashier'],
        ];
    }

    public static function register(): array
    {
        return [
            'brand' => 'MotoX',
            'tagline' => 'Automotive Management.',
            'benefits' => [
                ['title' => 'Organized Inventory', 'detail' => 'Track parts with precision and ease.', 'icon' => 'inventory'],
                ['title' => 'Efficient Work Orders', 'detail' => 'Streamline tasks from intake to completion.', 'icon' => 'wrench'],
                ['title' => 'Client Relationships', 'detail' => 'Manage customer details and service history.', 'icon' => 'customers'],
            ],
        ];
    }

    private static function money(float $amount): string
    {
        return '$'.number_format($amount, 2);
    }
}
