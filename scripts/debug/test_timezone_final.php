<?php

// Set PHP timezone before Laravel bootstrap
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== Timezone Configuration Test ===\n";

echo "PHP Timezone: " . date_default_timezone_get() . "\n";
echo "Laravel Config Timezone: " . config('app.timezone') . "\n";
echo "Current PHP Time: " . date('Y-m-d H:i:s') . "\n";

// Test database connection timezone
try {
    $dbTime = \DB::select("SELECT NOW() as db_time, CURRENT_TIMESTAMP as current_timestamp")[0];
    echo "Database NOW(): " . $dbTime->db_time . "\n";
    echo "Database CURRENT_TIMESTAMP: " . $dbTime->current_timestamp . "\n";
} catch (Exception $e) {
    echo "Database timezone test failed: " . $e->getMessage() . "\n";
}

// Test the specific delivery
$delivery = \App\Models\Deliveries\Delivery::find(162);
if ($delivery) {
    echo "\n=== Delivery Test ===\n";
    echo "Delivery: {$delivery->name}\n";
    echo "Scheduled At: {$delivery->scheduled_at}\n";
    echo "Current Laravel Time: " . \Carbon\Carbon::now()->toDateTimeString() . "\n";

    // Test the exact logic from PublicTokenLoginController
    $scheduledTime = strtotime($delivery->scheduled_at);
    $currentTime = strtotime('now');
    $timeDiff = $scheduledTime - $currentTime;

    echo "Scheduled Time (timestamp): $scheduledTime\n";
    echo "Current Time (timestamp): $currentTime\n";
    echo "Time Difference: $timeDiff seconds (" . round($timeDiff/60, 2) . " minutes)\n";

    $shouldGoToWaitingRoom = $delivery->automatic_start && $scheduledTime > $currentTime;
    echo "Should go to waiting room: " . ($shouldGoToWaitingRoom ? 'YES' : 'NO') . "\n";

    if ($shouldGoToWaitingRoom) {
        echo "✅ TOKEN SHOULD REDIRECT TO WAITING ROOM\n";
    } else {
        echo "❌ TOKEN WILL REDIRECT DIRECTLY TO EXAM\n";
    }
}

echo "\n=== Test Complete ===\n";