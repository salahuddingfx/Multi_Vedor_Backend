<?php
include 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sites = \App\Models\Site::all();
foreach ($sites as $s) {
    echo "Site: " . $s->name . " (Slug: " . $s->slug . ")\n";
    echo "Support Phone: " . ($s->settings['support_phone'] ?? 'NULL') . "\n";
    echo "Contact: " . ($s->settings['contact'] ?? 'NULL') . "\n";
    echo "Address: " . ($s->settings['address'] ?? 'NULL') . "\n";
    echo "-----------------------------------\n";
}
