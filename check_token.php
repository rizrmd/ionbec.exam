<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Deliveries\Delivery;
use App\Models\Takers\Taker;

echo "=== DEBUG HASH LOOKUP ===\n\n";

// Cari delivery dengan hash XjrMy4rQ
echo "1. Mencari delivery dengan hash XjrMy4rQ:\n";
$delivery = Delivery::where('hash', 'XjrMy4rQ')->first();

if ($delivery) {
    echo "   Delivery ditemukan:\n";
    echo "   - ID: {$delivery->id}\n";
    echo "   - Name: {$delivery->name}\n";
    echo "   - Hash: {$delivery->hash}\n";
    echo "   - Exam ID: {$delivery->exam_id}\n";
    echo "   - Client ID: {$delivery->client_id}\n";
    echo "   - Scheduled At: {$delivery->scheduled_at}\n";
    echo "   - Automatic Start: " . ($delivery->automatic_start ? 'Yes' : 'No') . "\n";
} else {
    echo "   Delivery TIDAK ditemukan dengan hash XjrMy4rQ\n";

    // Coba cari dengan MD5 lookup
    echo "\n   Mencoba MD5 lookup...\n";
    $deliveries = Delivery::all();
    foreach ($deliveries as $del) {
        $hash = md5($del->id . 'ionbec');
        if ($hash === 'XjrMy4rQ') {
            echo "   Delivery ditemukan dengan MD5 lookup:\n";
            echo "   - ID: {$del->id}\n";
            echo "   - Name: {$del->name}\n";
            echo "   - Hash: {$del->hash}\n";
            echo "   - Calculated MD5: {$hash}\n";
            $delivery = $del;
            break;
        }
    }
}

echo "\n2. Mencari taker dengan token 3AfDf:\n";
$takers = Taker::all();
$foundTaker = null;
foreach ($takers as $taker) {
    if ($taker->token === '3AfDf') {
        $foundTaker = $taker;
        echo "   Taker ditemukan:\n";
        echo "   - ID: {$taker->id}\n";
        echo "   - Email: {$taker->email}\n";
        echo "   - Name: {$taker->name}\n";
        echo "   - Token: {$taker->token}\n";
        break;
    }
}

if (!$foundTaker) {
    echo "   Taker TIDAK ditemukan dengan token 3AfDf\n";

    // Coba cari di pivot table delivery_taker
    echo "\n   Mencari di delivery_taker pivot table...\n";
    if ($delivery) {
        $takers = $delivery->takers;
        foreach ($takers as $taker) {
            $pivot = $taker->pivot;
            if ($pivot && $pivot->token === '3AfDf') {
                echo "   Token ditemukan di pivot table:\n";
                echo "   - Taker ID: {$taker->id}\n";
                echo "   - Taker Email: {$taker->email}\n";
                echo "   - Token: {$pivot->token}\n";
                $foundTaker = $taker;
                break;
            }
        }
    }
}

echo "\n3. Check relationship:\n";
if ($delivery && $foundTaker) {
    $isConnected = $delivery->takers()->where('taker_id', $foundTaker->id)->exists();
    echo "   Apakah taker terhubung dengan delivery: " . ($isConnected ? 'Ya' : 'Tidak') . "\n";

    if ($isConnected) {
        $pivot = $delivery->takers()->where('taker_id', $foundTaker->id)->first()->pivot;
        echo "   - Pivot Token: {$pivot->token}\n";
        echo "   - Created At: {$pivot->created_at}\n";
    }
}

echo "\n=== END DEBUG ===\n";