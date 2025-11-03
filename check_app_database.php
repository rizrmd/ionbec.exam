<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Exams\Item;

echo "=== APPLICATION DATABASE VERIFICATION ===\n\n";

// Get database connection info
$dbName = DB::connection()->getDatabaseName();
$dbHost = DB::connection()->getConfig('host');
echo "Database: $dbName\n";
echo "Host: $dbHost\n\n";

// Count items with specific IDs that should NOT exist
$deletedIds = [4, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15]; // The ones we deleted
$existingDeletedItems = Item::whereIn('id', $deletedIds)->get();

if ($existingDeletedItems->count() > 0) {
    echo "❌ PROBLEM FOUND! Items that should be deleted still exist:\n";
    foreach ($existingDeletedItems as $item) {
        echo "  - ID: {$item->id} - {$item->title}\n";
    }
} else {
    echo "✅ CONFIRMED: Deleted items (1-15) do not exist in this database\n";
}

// Show current first 10 items
echo "\nCurrent first 10 items in database:\n";
$firstItems = Item::orderBy('id')->limit(10)->get();
foreach ($firstItems as $index => $item) {
    echo sprintf("%2d. ID: %-3d - %s\n", $index + 1, $item->id, $item->title);
}

// Count total
$totalItems = Item::count();
echo "\nTotal items: $totalItems\n";

// Check if there are any items with ID < 16
$itemsUnder16 = Item::where('id', '<', 16)->count();
echo "Items with ID < 16: $itemsUnder16\n";

echo "\n=== ANALYSIS COMPLETE ===\n";

if ($itemsUnder16 > 0) {
    echo "⚠️  WARNING: There are still items with ID < 16 in the database!\n";
    echo "This explains why you still see the deleted question sets.\n";
} else {
    echo "✅ Database is clean - no items with ID < 16 found.\n";
    echo "If you still see deleted items, the issue might be:\n";
    echo "1. Your web application is using a different database\n";
    echo "2. Strong caching on the web server\n";
    echo "3. CDN or proxy cache\n";
}