<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Exams\Item;

echo "=== VERIFICATION AFTER DELETION ===\n\n";

// Get first 5 items
$firstItems = Item::orderBy('id')->limit(5)->get();

if ($firstItems->isEmpty()) {
    echo "No items found in database.\n";
} else {
    echo "First 5 question sets after deletion:\n\n";
    foreach ($firstItems as $index => $item) {
        echo sprintf("%2d. ID: %-3d - %s\n", $index + 1, $item->id, $item->title);
    }
}

$total = Item::count();
echo "\nTotal question sets remaining: $total\n";

echo "\n=== VERIFICATION COMPLETE ===\n";
