<?php

require_once "vendor/autoload.php";

$app = require_once "bootstrap/app.php";
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Fix Vignette Flag Script ===\n";
echo "This script will set is_vignette = true for vignette question sets\n\n";

// Define the vignette items that should have is_vignette = true
$vignetteItems = [
    'BE051125 - MCQ 1 & 2',
    'BE051125 - MCQ 9 & 10',
    'BE051125 - MCQ 19 & 20'
];

$updateCount = 0;

foreach ($vignetteItems as $title) {
    echo "Processing: {$title}\n";

    // Find the item
    $item = DB::table('items')
        ->where('title', $title)
        ->first();

    if ($item) {
        // Update is_vignette flag
        DB::table('items')
            ->where('id', $item->id)
            ->update(['is_vignette' => true]);

        echo "  Updated is_vignette = true for item ID: {$item->id}\n";
        $updateCount++;

        // Verify the update
        $updated = DB::table('items')
            ->where('id', $item->id)
            ->value('is_vignette');

        echo "  Verified: is_vignette = " . ($updated ? 'true' : 'false') . "\n";
    } else {
        echo "  ERROR: Item not found: {$title}\n";
    }

    echo "\n";
}

// Clear caches
echo "Clearing Laravel caches...\n";
shell_exec('php artisan cache:clear');
shell_exec('php artisan config:clear');
shell_exec('php artisan view:clear');
echo "Caches cleared.\n\n";

echo "=== Vignette Flag Fix Summary ===\n";
echo "Updated {$updateCount} items with is_vignette = true\n";
echo "Vignette flag fix completed!\n";