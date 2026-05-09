<?php
include 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$order = \App\Models\Order::with('site')->find(19);
if ($order) {
    echo "Order ID: " . $order->id . "\n";
    echo "Site: " . ($order->site->name ?? 'NULL') . "\n";
    $settings = $order->site->settings;
    echo "Settings Type: " . gettype($settings) . "\n";
    echo "Support Phone via array: " . ($settings['support_phone'] ?? 'NULL') . "\n";
    echo "Support Phone via object: " . ($settings->support_phone ?? 'NULL') . "\n";
} else {
    echo "Order 19 not found\n";
}
