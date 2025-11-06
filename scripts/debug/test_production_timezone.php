<?php

echo "Timezone: " . date_default_timezone_get() . "\n";
echo "Current Time: " . date('Y-m-d H:i:s') . "\n";

// Bootstrap Laravel to get config
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Laravel Config Timezone: " . config('app.timezone') . "\n";
echo "Database Config Timezone: " . config('database.connections.pgsql.timezone') . "\n";