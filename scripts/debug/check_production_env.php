<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Exams\Item;

echo "=== PRODUCTION ENVIRONMENT CHECK ===\n\n";

echo "APP_ENV: " . env('APP_ENV') . "\n";
echo "APP_URL: " . env('APP_URL') . "\n";
echo "DB_HOST: " . env('DB_HOST') . "\n";
echo "DB_DATABASE: " . env('DB_DATABASE') . "\n";
echo "Actual DB: " . DB::connection()->getDatabaseName() . "\n";

echo "\n=== ITEMS COUNT ===\n";
$totalItems = Item::count();
$itemsUnder16 = Item::where('id', '<', 16)->count();

echo "Total items: $totalItems\n";
echo "Items with ID < 16: $itemsUnder16\n";

if ($itemsUnder16 > 0) {
    echo "\n❌ PROBLEM: Production still has items with ID < 16!\n";
    $problemItems = Item::where('id', '<', 16)->orderBy('id')->get();
    foreach ($problemItems as $item) {
        echo "  ID: {$item->id} - {$item->title}\n";
    }
} else {
    echo "\n✅ Production database is clean!\n";
}

echo "\n=== END CHECK ===\n";