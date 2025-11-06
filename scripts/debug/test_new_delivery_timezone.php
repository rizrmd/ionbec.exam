<?php

// Set timezone
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== Testing New Delivery Timezone Behavior ===\n";

echo "Laravel Config Timezone: " . config('app.timezone') . "\n";
echo "PHP Current Time: " . date('Y-m-d H:i:s T') . "\n";

// Test database timezone setting
try {
    $result = \DB::select("SELECT NOW() as db_time")[0];
    echo "Database NOW(): " . $result->db_time . "\n";
} catch (Exception $e) {
    echo "Database query failed: " . $e->getMessage() . "\n";
}

// Simulate creating a new delivery
echo "\n=== Simulating New Delivery Creation ===\n";

$futureTime = \Carbon\Carbon::now()->addHours(2);
echo "If we create a new delivery scheduled for: " . $futureTime->toDateTimeString() . "\n";

// Test what gets stored in database
try {
    \DB::statement("INSERT INTO deliveries (name, exam_id, group_id, scheduled_at, automatic_start, duration, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [
        'Test Timezone Delivery',
        1, // dummy exam_id
        1, // dummy group_id
        $futureTime,
        true,
        60, // 60 minutes
        now(),
        now()
    ]);

    $newDeliveryId = \DB::getPdo()->lastInsertId();
    echo "✅ Created test delivery with ID: $newDeliveryId\n";

    // Check what was actually stored
    $stored = \DB::select("SELECT id, name, scheduled_at FROM deliveries WHERE id = ?", [$newDeliveryId])[0];
    echo "Stored scheduled_at: " . $stored->scheduled_at . "\n";

    // Test the waiting room logic for this new delivery
    $scheduledTime = strtotime($stored->scheduled_at);
    $currentTime = strtotime('now');
    $shouldGoToWaitingRoom = true && $scheduledTime > $currentTime; // automatic_start = true

    echo "Should go to waiting room: " . ($shouldGoToWaitingRoom ? 'YES' : 'NO') . "\n";

    // Clean up
    \DB::statement("DELETE FROM deliveries WHERE id = ?", [$newDeliveryId]);
    echo "✅ Cleaned up test delivery\n";

} catch (Exception $e) {
    echo "❌ Error creating test delivery: " . $e->getMessage() . "\n";
}

echo "\n=== Permanence Test ===\n";
echo "Laravel config/database.php timezone setting: " . config('database.connections.pgsql.timezone') . "\n";
echo "This setting will be applied on every Laravel request/connection.\n";

echo "\n=== Test Complete ===\n";