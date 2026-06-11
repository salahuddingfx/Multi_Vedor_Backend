<?php
// seed_custom_orders.php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReturn;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Bootstrapped Laravel successfully!\n";

$products = Product::all();
if ($products->isEmpty()) {
    echo "No products found in the database. Please run seeders first.\n";
    exit(1);
}

echo "Found " . $products->count() . " products.\n";

$statuses = ['placed', 'confirmed', 'packed', 'shipped', 'delivered', 'cancelled', 'returned'];
$paymentStatuses = ['unpaid', 'paid'];
$locations = ['dhaka', 'outside'];

// Dates configuration (Current time is June 11, 2026)
$dates = [
    // This week (June 8 - June 11, 2026)
    'this_week' => [
        '2026-06-11 10:30:00',
        '2026-06-10 14:15:00',
        '2026-06-09 09:45:00',
        '2026-06-08 16:20:00',
    ],
    // Last week (June 1 - June 7, 2026)
    'last_week' => [
        '2026-06-07 11:00:00',
        '2026-06-05 18:30:00',
        '2026-06-03 12:00:00',
        '2026-06-01 15:45:00',
    ],
    // Previous month (May 2026)
    'prev_month' => [
        '2026-05-28 14:00:00',
        '2026-05-25 10:30:00',
        '2026-05-20 16:00:00',
        '2026-05-15 09:00:00',
        '2026-05-10 13:15:00',
        '2026-05-05 11:45:00',
    ]
];

$orderCount = 0;
$returnCount = 0;

foreach ($dates as $period => $periodDates) {
    foreach ($periodDates as $dateTimeStr) {
        // Create 1-2 orders per date time
        $numOrders = rand(1, 2);
        for ($i = 0; $i < $numOrders; $i++) {
            // Pick a random product
            $product = $products->random();
            $siteId = $product->site_id;
            
            // Random details
            $qty = rand(1, 3);
            $price = (float)$product->price;
            $subtotal = $price * $qty;
            $deliveryCharge = rand(0, 1) ? 60 : 120;
            $discount = rand(0, 5) === 0 ? rand(20, 50) : 0;
            $totalAmount = $subtotal + $deliveryCharge - $discount;
            
            $status = $statuses[array_rand($statuses)];
            // Make sure completed or delivered is set for some to count as realized revenue
            if (rand(0, 2) === 0) {
                $status = 'delivered';
            }
            
            $paymentStatus = ($status === 'delivered') ? 'paid' : $paymentStatuses[array_rand($paymentStatuses)];
            
            $order = new Order();
            $order->fill([
                'site_id' => $siteId,
                'tracking_id' => 'TRK-' . strtoupper(bin2hex(random_bytes(4))),
                'customer_name' => 'Customer ' . rand(100, 999),
                'customer_phone' => '017' . rand(10000000, 99999999),
                'customer_email' => 'customer' . rand(100, 999) . '@example.com',
                'customer_address' => 'Test Address ' . rand(1, 50) . ', Dhaka',
                'customer_notes' => 'Seeded for date range check (' . $period . ')',
                'location' => $locations[array_rand($locations)],
                'subtotal' => $subtotal,
                'total_weight' => ($product->weight || 0.5) * $qty,
                'delivery_charge' => $deliveryCharge,
                'total_amount' => $totalAmount,
                'status' => $status,
                'payment_status' => $paymentStatus,
                'payment_method' => rand(0, 1) ? 'cod' : 'bkash',
                'transaction_id' => rand(0, 1) ? null : 'TXN' . strtoupper(bin2hex(random_bytes(5))),
                'sender_number' => rand(0, 1) ? null : '017' . rand(10000000, 99999999),
                'coupon_code' => $discount > 0 ? 'SEEDED50' : null,
                'discount_amount' => $discount
            ]);
            
            // Set timestamps explicitly
            $order->timestamps = false;
            $order->created_at = $dateTimeStr;
            $order->updated_at = $dateTimeStr;
            $order->save();
            
            // Create OrderItem
            $item = new OrderItem();
            $item->fill([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku || ('SKU-' . rand(1000, 9999)),
                'price' => $price,
                'quantity' => $qty,
                'weight' => $product->weight || 0.5
            ]);
            $item->timestamps = false;
            $item->created_at = $dateTimeStr;
            $item->updated_at = $dateTimeStr;
            $item->save();
            
            $orderCount++;
            
            // Create returns if order is returned or just random
            if ($status === 'returned' || (rand(0, 8) === 0 && $status !== 'cancelled')) {
                $ret = new ProductReturn();
                $ret->fill([
                    'site_id' => $siteId,
                    'product_id' => $product->id,
                    'quantity' => rand(1, $qty),
                    'amount' => $price * rand(1, $qty),
                    'order_id' => $order->id,
                    'reason' => 'Seeded return testing',
                    'type' => rand(0, 1) ? 'replacement' : 'refund',
                    'return_date' => date('Y-m-d', strtotime($dateTimeStr))
                ]);
                $ret->timestamps = false;
                $ret->created_at = $dateTimeStr;
                $ret->updated_at = $dateTimeStr;
                $ret->save();
                
                $returnCount++;
            }
        }
    }
}

echo "Successfully seeded $orderCount orders and $returnCount returns!\n";
