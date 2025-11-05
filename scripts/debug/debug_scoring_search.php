<?php

// Disable error reporting for deprecated warnings
error_reporting(E_ERROR | E_PARSE);

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get delivery by hash
$deliveryHash = 'y3Ed7rzD';
$delivery = \App\Models\Deliveries\Delivery::byHash($deliveryHash);

echo "Delivery ID: {$delivery->id}\n";
echo "Delivery Name: {$delivery->name}\n";
echo "Exam ID: {$delivery->exam_id}\n\n";

// Get all attempts for this delivery
echo "=== ALL ATTEMPTS ===\n";
$allAttempts = \App\Models\Attempts\Attempt::query()
    ->where('delivery_id', $delivery->id)
    ->where('exam_id', $delivery->exam_id)
    ->with('taker')
    ->get();

echo "Total attempts: " . $allAttempts->count() . "\n\n";

foreach ($allAttempts as $attempt) {
    echo "Attempt ID: {$attempt->id}\n";
    echo "Taker: " . ($attempt->taker ? $attempt->taker->name : 'No taker') . "\n";
    echo "Email: " . ($attempt->taker ? $attempt->taker->email : 'No email') . "\n";
    echo "Score: {$attempt->score}\n";
    echo "---\n";
}

// Now test search with "sari"
echo "\n=== SEARCH TEST: 'sari' ===\n";
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
    ->with('taker')
    ->get();

echo "Found {$searchAttempts->count()} attempts matching 'sari'\n\n";

foreach ($searchAttempts as $attempt) {
    echo "Attempt ID: {$attempt->id}\n";
    echo "Taker: " . ($attempt->taker ? $attempt->taker->name : 'No taker') . "\n";
    echo "Email: " . ($attempt->taker ? $attempt->taker->email : 'No email') . "\n";
    echo "---\n";
}

// Check if there are takers in the group with "sari" in their name
echo "\n=== TAKERS IN GROUP ===\n";
$group = $delivery->group;
echo "Group ID: {$group->id}\n";
echo "Group Name: {$group->name}\n\n";

$takersInGroup = \App\Models\Takers\Taker::query()
    ->whereHas('groups', function ($q) use ($group) {
        $q->where('group_id', $group->id);
    })
    ->where(function ($q) use ($searchQuery) {
        $q->where('name', 'like', "%{$searchQuery}%");
        $q->orWhere('email', 'like', "%{$searchQuery}%");
    })
    ->get();

echo "Found {$takersInGroup->count()} takers in group matching 'sari'\n\n";

foreach ($takersInGroup as $taker) {
    echo "Taker ID: {$taker->id}\n";
    echo "Name: {$taker->name}\n";
    echo "Email: {$taker->email}\n";

    // Check if they have attempt
    $hasAttempt = \App\Models\Attempts\Attempt::query()
        ->where('delivery_id', $delivery->id)
        ->where('attempted_by', $taker->id)
        ->exists();

    echo "Has attempt: " . ($hasAttempt ? 'YES' : 'NO') . "\n";
    echo "---\n";
}
