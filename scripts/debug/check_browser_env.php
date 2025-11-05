<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ENVIRONMENT CHECK ===\n\n";

echo "APP_ENV: " . env('APP_ENV') . "\n";
echo "APP_DEBUG: " . env('APP_DEBUG') . "\n";
echo "APP_URL: " . env('APP_URL') . "\n";
echo "DB_CONNECTION: " . env('DB_CONNECTION') . "\n";
echo "DB_HOST: " . env('DB_HOST') . "\n";
echo "DB_DATABASE: " . env('DB_DATABASE') . "\n";
echo "DB_PORT: " . env('DB_PORT') . "\n";

echo "\n=== CURRENT ACTUAL DB CONNECTION ===\n";
echo "Database: " . DB::connection()->getDatabaseName() . "\n";
echo "Host: " . DB::connection()->getConfig('host') . "\n";
echo "Port: " . DB::connection()->getConfig('port') . "\n";

echo "\n=== QUESTION COUNTS ===\n";
$totalItems = \App\Models\Exams\Item::count();
echo "Total items: $totalItems\n";

$itemsUnder16 = \App\Models\Exams\Item::where('id', '<', 16)->count();
echo "Items with ID < 16: $itemsUnder16\n";

// Check specific items that should have been deleted
$deletedIds = [4, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15];
$existingDeleted = \App\Models\Exams\Item::whereIn('id', $deletedIds)->count();
echo "Deleted items still existing: $existingDeleted\n";

echo "\n=== URL TO CHECK IN BROWSER ===\n";
echo "Add this to your browser to see environment info:\n";
echo env('APP_URL') . "/check-env-debug\n";

echo "\n=== INSTRUCTIONS ===\n";
echo "1. If you access the web application on a different URL/domain\n";
echo "2. Or if your web server is using a different .env file\n";
echo "3. The browser application might be connected to a DIFFERENT database\n\n";
echo "SOLUTIONS:\n";
echo "1. Check if your web server is using the same .env file\n";
echo "2. Deploy the changes to production server\n";
echo "3. Clear production server cache\n";
echo "4. Check if you're accessing localhost vs production server\n";