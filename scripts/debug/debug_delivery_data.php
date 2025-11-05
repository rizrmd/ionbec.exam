<?php

error_reporting(E_ERROR | E_PARSE);

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$deliveryHash = 'y3Ed7rzD';

// Find delivery using DB query
$delivery = DB::table('deliveries')
    ->where('hash', $deliveryHash)
    ->first();

if (!$delivery) {
    echo "Delivery not found with hash: {$deliveryHash}\n";
    exit;
}

echo "✓ Delivery Found\n";
echo "ID: {$delivery->id}\n";
echo "Name: {$delivery->name}\n";
echo "Exam ID: {$delivery->exam_id}\n";
echo "Group ID: {$delivery->group_id}\n\n";

// Get all attempts
echo "=== ALL ATTEMPTS ===\n";
$attempts = DB::table('attempts')
    ->where('delivery_id', $delivery->id)
    ->where('exam_id', $delivery->exam_id)
    ->get();

echo "Total attempts: {$attempts->count()}\n\n";

foreach ($attempts as $attempt) {
    $taker = DB::table('takers')
        ->where('id', $attempt->attempted_by)
        ->first();

    echo "Attempt ID: {$attempt->id}\n";
    echo "  Taker ID: {$attempt->attempted_by}\n";
    echo "  Name: " . ($taker ? $taker->name : 'NULL') . "\n";
    echo "  Email: " . ($taker ? $taker->email : 'NULL') . "\n";
    echo "  Client ID (attempt): {$attempt->client_id}\n";
    echo "  Client ID (taker): " . ($taker ? $taker->client_id : 'NULL') . "\n\n";
}

// Search for "sari" using JOIN
echo "=== SEARCH TEST: 'sari' ===\n";
$searchResults = DB::table('attempts')
    ->join('takers', 'attempts.attempted_by', '=', 'takers.id')
    ->where('attempts.delivery_id', $delivery->id)
    ->where('attempts.exam_id', $delivery->exam_id)
    ->where(function ($q) {
        $q->where('takers.name', 'like', '%sari%')
          ->orWhere('takers.email', 'like', '%sari%');
    })
    ->select('attempts.*', 'takers.name', 'takers.email')
    ->get();

echo "Found: {$searchResults->count()} results\n\n";

foreach ($searchResults as $result) {
    echo "Attempt ID: {$result->id}\n";
    echo "  Name: {$result->name}\n";
    echo "  Email: {$result->email}\n\n";
}

// Check if there are takers with "sari" in the group
echo "=== TAKERS IN GROUP WITH 'sari' ===\n";
$takersInGroup = DB::table('group_taker')
    ->join('takers', 'group_taker.taker_id', '=', 'takers.id')
    ->where('group_taker.group_id', $delivery->group_id)
    ->where(function ($q) {
        $q->where('takers.name', 'like', '%sari%')
          ->orWhere('takers.email', 'like', '%sari%');
    })
    ->select('takers.*')
    ->get();

echo "Found: {$takersInGroup->count()} takers in group\n\n";

foreach ($takersInGroup as $taker) {
    echo "Taker ID: {$taker->id}\n";
    echo "  Name: {$taker->name}\n";
    echo "  Email: {$taker->email}\n";

    // Check if they have attempted
    $hasAttempt = DB::table('attempts')
        ->where('delivery_id', $delivery->id)
        ->where('attempted_by', $taker->id)
        ->exists();

    echo "  Has Attempt: " . ($hasAttempt ? 'YES' : 'NO') . "\n\n";
}

// Show first 5 takers to see what names we have
echo "=== FIRST 5 TAKERS IN GROUP (for reference) ===\n";
$firstTakers = DB::table('group_taker')
    ->join('takers', 'group_taker.taker_id', '=', 'takers.id')
    ->where('group_taker.group_id', $delivery->group_id)
    ->select('takers.id', 'takers.name', 'takers.email')
    ->limit(5)
    ->get();

foreach ($firstTakers as $taker) {
    echo "  - {$taker->name} ({$taker->email})\n";
}
