<?php
include 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sites = \App\Models\Site::all();
foreach ($sites as $s) {
    echo "ID: " . $s->id . " | Name: " . $s->name . " | Slug: " . $s->slug . "\n";
}
