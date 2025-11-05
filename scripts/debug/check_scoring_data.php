<?php

error_reporting(E_ERROR | E_PARSE);

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Find delivery ID from hash
$deliveryId = DB::table('deliveries')
    ->select('id', 'name', 'exam_id', 'group_id')
    ->whereRaw("MD5(CONCAT(id::text, 'ionbec')) = 'y3Ed7rzD'")
    ->first();

if (!$deliveryId) {
    echo "Delivery not found\n";
    exit;
}

echo "Delivery ID: {$deliveryId->id}\n";
echo "Delivery Name: {$deliveryId->name}\n";
echo "Exam ID: {$deliveryId->exam_id}\n";
echo "Group ID: {$deliveryId->group_id}\n\n";

// Get all attempts for this delivery
echo "=== ALL ATTEMPTS ===\n";
$allAttempts = DB::table('attempts')
    ->select('id', 'attempted_by', 'score', 'progress', 'finish_scoring')
    ->where('delivery_id', $deliveryId->id)
    ->where('exam_id', $deliveryId->exam_id)
    ->get();

echo "Total attempts: " . $allAttempts->count() . "\n\n";

foreach ($allAttempts as $attempt) {
    $taker = DB::table('takers')
        ->select('name', 'email')
        ->where('id', $attempt->attempted_by)
        ->first();

    echo "Attempt ID: {$attempt->id}\n";
    echo "Taker: " . ($taker ? $taker->name : 'No taker') . "\n";
    echo "Email: " . ($taker ? $taker->email : 'No email') . "\n";
    echo "Score: {$attempt->score}\n";
    echo "---\n";
}

// Test search with "sari"
echo "\n=== SEARCH TEST: 'sari' (via attempts table join) ===\n";
$searchQuery = 'sari';
$searchAttempts = DB::table('attempts')
    ->join('takers', 'attempts.attempted_by', '=', 'takers.id')
    ->select('attempts.id', 'attempts.score', 'takers.name', 'takers.email')
    ->where('attempts.delivery_id', $deliveryId->id)
    ->where('attempts.exam_id', $deliveryId->exam_id)
    ->where(function ($query) use ($searchQuery) {
        $query->where('takers.name', 'like', "%{$searchQuery}%")
            ->orWhere('takers.email', 'like', "%{$searchQuery}%");
    })
    ->get();

echo "Found {$searchAttempts->count()} attempts matching 'sari'\n\n";

foreach ($searchAttempts as $attempt) {
    echo "Attempt ID: {$attempt->id}\n";
    echo "Taker: {$attempt->name}\n";
    echo "Email: {$attempt->email}\n";
    echo "---\n";
}

// Check takers in group
echo "\n=== TAKERS IN GROUP ===\n";
$takersInGroup = DB::table('group_taker')
    ->join('takers', 'group_taker.taker_id', '=', 'takers.id')
    ->select('takers.id', 'takers.name', 'takers.email')
    ->where('group_taker.group_id', $deliveryId->group_id)
    ->where(function ($query) use ($searchQuery) {
        $query->where('takers.name', 'like', "%{$searchQuery}%")
            ->orWhere('takers.email', 'like', "%{$searchQuery}%");
    })
    ->get();

echo "Found {$takersInGroup->count()} takers in group matching 'sari'\n\n";

foreach ($takersInGroup as $taker) {
    echo "Taker ID: {$taker->id}\n";
    echo "Name: {$taker->name}\n";
    echo "Email: {$taker->email}\n";

    // Check if they have attempt
    $hasAttempt = DB::table('attempts')
        ->where('delivery_id', $deliveryId->id)
        ->where('attempted_by', $taker->id)
        ->exists();

    echo "Has attempt: " . ($hasAttempt ? 'YES' : 'NO') . "\n";
    echo "---\n";
}
