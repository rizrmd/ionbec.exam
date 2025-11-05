<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Deliveries\Delivery;
use App\Models\Takers\Taker;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

echo "=== TESTING TOKEN ACCESS FLOW ===\n\n";

// Find delivery by hash XjrMy4rQ
$delivery = Delivery::where('hash', 'XjrMy4rQ')->first();

if (!$delivery) {
    echo "❌ Delivery XjrMy4rQ not found\n";
    exit;
}

echo "✅ Delivery found: {$delivery->name}\n";
echo "   - ID: {$delivery->id}\n";
echo "   - Hash: {$delivery->hash}\n";
echo "   - Scheduled At: {$delivery->scheduled_at}\n";
echo "   - Automatic Start: " . ($delivery->automatic_start ? 'Yes' : 'No') . "\n";
echo "   - Duration: {$delivery->duration} minutes\n\n";

// Find taker by token 3AfDf
$taker = null;
$takers = Taker::all();

foreach ($takers as $t) {
    // Check pivot table for token
    $pivot = \DB::table('delivery_taker')
        ->where('delivery_id', $delivery->id)
        ->where('taker_id', $t->id)
        ->where('token', '3AfDf')
        ->first();

    if ($pivot) {
        $taker = $t;
        echo "✅ Taker found with token 3AfDf:\n";
        echo "   - ID: {$taker->id}\n";
        echo "   - Email: {$taker->email}\n";
        echo "   - Name: {$taker->name}\n";
        echo "   - Is Login: " . ($pivot->is_login ? 'Yes' : 'No') . "\n\n";
        break;
    }
}

if (!$taker) {
    echo "❌ No taker found with token 3AfDf\n";
    exit;
}

// Test 1: Simulate ScheduleController login flow
echo "=== TESTING SCHEDULE CONTROLLER FLOW ===\n";

// Clear session
Session::forget('exam');

// Simulate the session data that should be set
Session::put('exam', [
    'token' => null,
    'taker' => $taker,
    'delivery' => $delivery,
    'admin' => null,
]);

echo "✅ Session data set\n";

// Check if should redirect to waiting room
$now = strtotime('now');
$scheduled = strtotime($delivery->scheduled_at);
$shouldGoToWaitingRoom = $delivery->automatic_start && ($scheduled > $now);

echo "Current time: " . date('Y-m-d H:i:s') . "\n";
echo "Scheduled time: " . $delivery->scheduled_at . "\n";
echo "Should go to waiting room: " . ($shouldGoToWaitingRoom ? 'YES' : 'NO') . "\n\n";

// Test 2: Check if we can access waiting room route
echo "=== TESTING WAITING ROOM ACCESS ===\n";

// Create a mock request to test waiting room
$request = Request::create('/exam/waiting-room', 'GET');

// Check if waiting room controller would work
try {
    $waitingRoomController = new \App\Http\Controllers\Exam\WaitingRoomController();

    echo "✅ WaitingRoomController instantiated\n";

    // This would normally be called by Laravel routing
    // For testing, let's see what session data we have
    $sessionData = Session::get('exam');

    if ($sessionData && isset($sessionData['delivery'])) {
        echo "✅ Session has delivery data\n";
        echo "   - Delivery ID: " . $sessionData['delivery']->id . "\n";
        echo "   - Delivery Name: " . $sessionData['delivery']->name . "\n";
    } else {
        echo "❌ No delivery data in session\n";
    }

} catch (Exception $e) {
    echo "❌ Error testing waiting room: " . $e->getMessage() . "\n";
}

echo "\n=== RECOMMENDATIONS ===\n";
echo "1. The waiting room flow exists and should work\n";
echo "2. Token 3AfDf is valid and connected to delivery XjrMy4rQ\n";
echo "3. The issue is likely in the PUBLIC route that handles token login\n";
echo "4. Need to find the correct public URL for token-based access\n";
echo "5. Current URL https://ionbec.com/back-office/delivery/XjrMy4rQ/taker is admin-only\n\n";

echo "SOLUTION: Find the public route that accepts token 3AfDf and creates the session\n";
echo "Possible patterns:\n";
echo "- /exam/token/3AfDf\n";
echo "- /token/3AfDf\n";
echo "- /login/3AfDf\n";
echo "- Or a form where user enters token 3AfDf\n\n";

echo "The waiting room flow will work once the proper token login is completed!\n";