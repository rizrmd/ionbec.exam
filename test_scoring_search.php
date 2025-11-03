<?php

error_reporting(E_ERROR | E_PARSE);

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

try {
    // Get delivery by hash using proper method
    $deliveryHash = 'y3Ed7rzD';

    echo "Searching for delivery with hash: {$deliveryHash}\n\n";

    // Use the byHash method from HashableId trait
    $delivery = \App\Models\Deliveries\Delivery::withoutGlobalScope(\App\Scopes\ClientScope::class)
        ->where('hash', $deliveryHash)
        ->first();

    if (!$delivery) {
        echo "Delivery not found by hash column. Trying byHash method...\n";
        $delivery = \App\Models\Deliveries\Delivery::byHash($deliveryHash);
    }

    if (!$delivery) {
        echo "ERROR: Delivery still not found!\n";

        // Debug: show some deliveries
        echo "\nShowing first 5 deliveries:\n";
        $deliveries = \App\Models\Deliveries\Delivery::withoutGlobalScope(\App\Scopes\ClientScope::class)
            ->limit(5)
            ->get(['id', 'hash', 'name']);

        foreach ($deliveries as $d) {
            echo "ID: {$d->id}, Hash: {$d->hash}, Name: {$d->name}\n";
        }

        exit;
    }

    echo "✓ Delivery Found!\n";
    echo "ID: {$delivery->id}\n";
    echo "Name: {$delivery->name}\n";
    echo "Hash: {$delivery->hash}\n";
    echo "Exam ID: {$delivery->exam_id}\n";
    echo "Group ID: {$delivery->group_id}\n\n";

    // Test the current controller query WITHOUT search
    echo "=== TEST 1: Get all attempts (no search) ===\n";
    $allAttempts = \App\Models\Attempts\Attempt::query()
        ->where('delivery_id', $delivery->id)
        ->where('exam_id', $delivery->exam_id)
        ->get();

    echo "Total attempts: {$allAttempts->count()}\n\n";

    // Show some attempts
    foreach ($allAttempts->take(5) as $attempt) {
        $taker = \App\Models\Takers\Taker::withoutGlobalScope(\App\Scopes\ClientScope::class)
            ->find($attempt->attempted_by);

        echo "  - Attempt ID: {$attempt->id}\n";
        echo "    Taker: " . ($taker ? $taker->name : 'NULL') . "\n";
        echo "    Email: " . ($taker ? $taker->email : 'NULL') . "\n\n";
    }

    // Test the FIXED search query
    echo "=== TEST 2: Search for 'sari' (FIXED query) ===\n";
    $searchQuery = 'sari';

    $searchAttempts = \App\Models\Attempts\Attempt::query()
        ->where('delivery_id', $delivery->id)
        ->where('exam_id', $delivery->exam_id)
        ->whereHas('taker', function ($q) use ($searchQuery) {
            $q->where(function ($subQ) use ($searchQuery) {
                $subQ->where('name', 'like', "%{$searchQuery}%");
                $subQ->orWhere('email', 'like', "%{$searchQuery}%");
            });
        })
        ->get();

    echo "Found: {$searchAttempts->count()} attempts\n\n";

    foreach ($searchAttempts as $attempt) {
        $taker = \App\Models\Takers\Taker::withoutGlobalScope(\App\Scopes\ClientScope::class)
            ->find($attempt->attempted_by);

        echo "  - Attempt ID: {$attempt->id}\n";
        echo "    Taker: " . ($taker ? $taker->name : 'NULL') . "\n";
        echo "    Email: " . ($taker ? $taker->email : 'NULL') . "\n\n";
    }

    // Test the OLD (broken) search query
    echo "=== TEST 3: Search for 'sari' (OLD BROKEN query) ===\n";

    $oldSearchAttempts = \App\Models\Attempts\Attempt::query()
        ->where('delivery_id', $delivery->id)
        ->where('exam_id', $delivery->exam_id)
        ->whereHas('taker', function ($q) use ($searchQuery) {
            $q->where('name', 'like', "%{$searchQuery}%");
            $q->orWhere('email', 'like', "%{$searchQuery}%");
        })
        ->get();

    echo "Found: {$oldSearchAttempts->count()} attempts\n\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
