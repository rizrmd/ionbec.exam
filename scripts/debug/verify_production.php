<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Exams\Item;

echo "=== PRODUCTION VERIFICATION ===\n\n";

// Count items with ID < 16
$itemsUnder16 = Item::where('id', '<', 16)->count();
echo "Items with ID < 16: $itemsUnder16\n";

// Check specific deleted IDs
$deletedIds = range(1, 15);
$existingDeleted = Item::whereIn('id', $deletedIds)->count();
echo "Deleted items still existing: $existingDeleted\n";

// Get current first 5 items
$firstItems = Item::orderBy('id')->limit(5)->get();
echo "\nFirst 5 items:\n";
foreach ($firstItems as $index => $item) {
    echo sprintf("%2d. ID: %-3d - %s\n", $index + 1, $item->id, $item->title);
}

$totalItems = Item::count();
echo "\nTotal items: $totalItems\n";

if ($itemsUnder16 === 0) {
    echo "\n✅ PRODUCTION DATABASE IS CLEAN!\n";
    echo "Question sets 1-15 have been successfully deleted.\n";
} else {
    echo "\n❌ PRODUCTION DATABASE STILL HAS OLD ITEMS!\n";
    echo "Items with ID < 16 still exist: $itemsUnder16\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";