<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== Testing Token Flow Debug ===\n";

// Test with the future scheduled delivery token
$token = 'ykTcA';

echo "Testing token: $token\n";

// Check delivery_taker record
$deliveryTaker = \DB::table('delivery_taker')
    ->where('token', strtoupper($token))
    ->first();

if (!$deliveryTaker) {
    echo "❌ Token not found in delivery_taker table\n";
    exit;
}

echo "✅ Token found in delivery_taker table\n";
echo "   Delivery ID: {$deliveryTaker->delivery_id}\n";
echo "   Taker ID: {$deliveryTaker->taker_id}\n";
echo "   Is Login: " . ($deliveryTaker->is_login ? 'true' : 'false') . "\n";

// Get delivery details
$delivery = \App\Models\Deliveries\Delivery::find($deliveryTaker->delivery_id);

if (!$delivery) {
    echo "❌ Delivery not found\n";
    exit;
}

echo "✅ Delivery found\n";
echo "   Name: {$delivery->name}\n";
echo "   Scheduled At: {$delivery->scheduled_at}\n";
echo "   Automatic Start: " . ($delivery->automatic_start ? 'true' : 'false') . "\n";
echo "   Is Anytime: " . ($delivery->is_anytime ? 'true' : 'false') . "\n";

// Check time logic
$scheduledTime = strtotime($delivery->scheduled_at);
$currentTime = strtotime('now');
$timeDiff = $scheduledTime - $currentTime;

echo "   Scheduled Time (timestamp): $scheduledTime\n";
echo "   Current Time (timestamp): $currentTime\n";
echo "   Time Difference (seconds): $timeDiff\n";
echo "   Time Difference (minutes): " . round($timeDiff / 60, 2) . "\n";

// Test the condition from PublicTokenLoginController
$shouldGoToWaitingRoom = $delivery->automatic_start && $scheduledTime > $currentTime;

echo "\n=== Routing Decision ===\n";
echo "Condition: automatic_start=true && scheduled_at > now\n";
echo "automatic_start: " . ($delivery->automatic_start ? 'true' : 'false') . "\n";
echo "scheduled_at > now: " . ($scheduledTime > $currentTime ? 'true' : 'false') . "\n";
echo "Result: " . ($shouldGoToWaitingRoom ? 'Go to Waiting Room' : 'Go Directly to Exam') . "\n";

if ($shouldGoToWaitingRoom) {
    echo "✅ SHOULD redirect to waiting room\n";
    echo "   Route: exam.waiting-room (/exam/waiting-room)\n";
} else {
    echo "❌ WILL bypass waiting room\n";
    echo "   Route: /exam (exam.main)\n";
}

// Check if there are any other factors
echo "\n=== Additional Checks ===\n";

if ($delivery->ended_at && now()->isAfter($delivery->ended_at)) {
    echo "❌ Delivery has ended at {$delivery->ended_at}\n";
} else {
    echo "✅ Delivery is still active\n";
}

if ($delivery->is_anytime) {
    echo "ℹ️  This is an 'anytime' delivery - should be accessible anytime\n";
}

echo "\n=== Debug Complete ===\n";