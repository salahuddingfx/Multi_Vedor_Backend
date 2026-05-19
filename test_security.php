<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Site;

$site = Site::find(1);
$settings = $site->settings;
$settings['security'] = [
    'inactivity_timeout_enabled' => true,
    'inactivity_timeout' => 15,
    'working_hours_enabled' => false,
    'working_hours_start' => '09:00',
    'working_hours_end' => '18:00',
    'working_days' => [0, 1, 2, 3, 4, 5, 6]
];
$site->update(['settings' => $settings]);

// Clear cache
$siteId = 1;
\Illuminate\Support\Facades\Cache::forget("site_settings_{$siteId}");

echo "Database restored. Working hours restriction disabled.\n";
