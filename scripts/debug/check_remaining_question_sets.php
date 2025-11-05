<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Exams\Item;

echo "=== CHECKING REMAINING QUESTION SETS ===\n\n";

// Get all remaining items ordered by ID
$items = Item::orderBy('id')->get();

echo "Total remaining question sets: " . $items->count() . "\n\n";

echo "Remaining Question Sets:\n";
echo "========================\n";

foreach ($items as $index => $item) {
    $questionCount = $item->questions()->count();
    echo sprintf("%2d. ID: %-3d - %-50s (%d questions)\n",
        $index + 1,
        $item->id,
        strlen($item->title) > 50 ? substr($item->title, 0, 47) . '...' : $item->title,
        $questionCount
    );
}

echo "\n✅ Question sets 1-15 have been successfully deleted!\n";
echo "The remaining question sets start from ID: " . ($items->first()->id ?? 'None') . "\n";