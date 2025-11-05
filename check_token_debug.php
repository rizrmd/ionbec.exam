<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TOKEN DEBUG FOR nVX1A ===\n\n";

use App\Models\Deliveries\Delivery;
use App\Models\Takers\Taker;

// Step 1: Check delivery y3EdNgGz
echo "1. Checking delivery y3EdNgGz:\n";
$delivery = Delivery::where('hash', 'y3EdNgGz')->first();

if (!$delivery) {
    echo "   ❌ Delivery y3EdNgGz NOT FOUND!\n";
    echo "   This could be the main issue - the delivery hash doesn't exist.\n\n";
    exit;
}

echo "   ✅ Delivery found:\n";
echo "   - ID: {$delivery->id}\n";
echo "   - Name: {$delivery->name}\n";
echo "   - Hash: {$delivery->hash}\n";
echo "   - Scheduled At: {$delivery->scheduled_at}\n";
echo "   - Ended At: " . ($delivery->ended_at ? $delivery->ended_at : 'NULL') . "\n";
echo "   - Status: " . ($delivery->ended_at && now()->isAfter($delivery->ended_at) ? 'EXPIRED' : 'ACTIVE') . "\n\n";

// Step 2: Check all tokens for this delivery
echo "2. Checking all tokens for this delivery:\n";
$takers = $delivery->takers()->withPivot(['token', 'is_login', 'created_at'])->get();

if ($takers->count() === 0) {
    echo "   ❌ No takers found for this delivery!\n";
    echo "   This means no tokens have been generated for this delivery.\n\n";
} else {
    echo "   Found {$takers->count()} taker(s):\n";
    foreach ($takers as $taker) {
        $pivot = $taker->pivot;
        echo "   - Taker: {$taker->name} ({$taker->email})\n";
        echo "     Token: {$pivot->token}\n";
        echo "     is_login: " . ($pivot->is_login ? 'YES' : 'NO') . "\n";
        echo "     Created: {$pivot->created_at}\n";
        echo "     -------------------\n";
    }
}

// Step 3: Check specifically for token nVX1A
echo "\n3. Checking specifically for token nVX1A:\n";
$tokenExists = \DB::table('delivery_taker')
    ->where('token', 'nVX1A')
    ->exists();

if ($tokenExists) {
    echo "   ✅ Token nVX1A EXISTS in database!\n";

    // Get details
    $tokenInfo = \DB::table('delivery_taker')
        ->where('token', 'nVX1A')
        ->first();

    echo "   - Token: {$tokenInfo->token}\n";
    echo "   - Delivery ID: {$tokenInfo->delivery_id}\n";
    echo "   - Taker ID: {$tokenInfo->taker_id}\n";
    echo "   - is_login: " . ($tokenInfo->is_login ? 'YES' : 'NO') . "\n";
    echo "   - Created: {$tokenInfo->created_at}\n";

    // Check if delivery matches
    if ($tokenInfo->delivery_id == $delivery->id) {
        echo "   ✅ Token belongs to this delivery!\n";
    } else {
        echo "   ❌ Token belongs to DIFFERENT delivery (ID: {$tokenInfo->delivery_id})!\n";
        echo "   This explains why it's invalid for delivery y3EdNgGz.\n";
    }

    // Check if delivery is still active
    if ($delivery->ended_at && now()->isAfter($delivery->ended_at)) {
        echo "   ❌ DELIVERY HAS EXPIRED!\n";
        echo "   Delivery ended at: {$delivery->ended_at}\n";
        echo "   Current time: " . now() . "\n";
        echo "   Expired tokens cannot be used.\n";
    }

} else {
    echo "   ❌ Token nVX1A NOT FOUND in database!\n";
    echo "   This is the main reason it's invalid - the token doesn't exist.\n";
}

echo "\n=== POSSIBLE REASONS FOR INVALID TOKEN ===\n";
echo "1. Token doesn't exist in delivery_taker table\n";
echo "2. Token belongs to different delivery\n";
echo "3. Delivery has expired (ended_at < now)\n";
echo "4. Delivery hash y3EdNgGz doesn't exist\n";
echo "5. Token has been used and is_login is already true\n";

echo "\n=== FILES INVOLVED IN TOKEN PROCESS ===\n";
echo "1. PublicTokenLoginController.php - Main token authentication logic\n";
echo "2. routes.php - Route definitions for token access\n";
echo "3. delivery_taker table - Pivot table storing tokens\n";
echo "4. deliveries table - Main delivery information\n";
echo "5. takers table - User information\n";

echo "\n=== DEBUG COMPLETE ===\n";