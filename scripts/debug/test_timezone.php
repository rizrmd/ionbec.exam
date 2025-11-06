<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== Timezone Debug ===\n";

echo "Laravel App Timezone: " . config('app.timezone') . "\n";
echo "PHP Default Timezone: " . date_default_timezone_get() . "\n";
echo "Current Laravel Now: " . \Carbon\Carbon::now()->toDateTimeString() . "\n";
echo "Current PHP Now: " . date('Y-m-d H:i:s') . "\n";

// Get the delivery
$delivery = \App\Models\Deliveries\Delivery::find(162);

if ($delivery) {
    echo "\nDelivery Info:\n";
    echo "Name: {$delivery->name}\n";
    echo "Scheduled At (DB): {$delivery->scheduled_at}\n";
    echo "Scheduled At (Carbon): " . $delivery->scheduled_at->toDateTimeString() . "\n";

    // Test the exact logic from PublicTokenLoginController
    $scheduledTime = strtotime($delivery->scheduled_at);
    $currentTime = strtotime('now');
    $timeDiff = $scheduledTime - $currentTime;

    echo "\nTime Comparison:\n";
    echo "Scheduled Time (strtotime): $scheduledTime\n";
    echo "Current Time (strtotime): $currentTime\n";
    echo "Time Difference: $timeDiff seconds (" . round($timeDiff/60, 2) . " minutes)\n";
    echo "Should go to waiting room: " . ($delivery->automatic_start && $scheduledTime > $currentTime ? 'YES' : 'NO') . "\n";

    // Also test with Carbon
    $now = \Carbon\Carbon::now();
    $scheduled = $delivery->scheduled_at;

    echo "\nCarbon Comparison:\n";
    echo "Current Carbon: " . $now->toDateTimeString() . "\n";
    echo "Scheduled Carbon: " . $scheduled->toDateTimeString() . "\n";
    echo "Is scheduled > now: " . ($scheduled->gt($now) ? 'YES' : 'NO') . "\n";
    echo "Minutes until start: " . $scheduled->diffInMinutes($now) . "\n";
}