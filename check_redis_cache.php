<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Redis;
use App\Models\Exams\Item;

echo "=== CHECKING REDIS CACHE ===\n\n";

try {
    // Check Redis connection
    $redis = Redis::connection();
    $redis->ping();
    echo "✅ Redis connection successful\n";

    // Check if there are any cached items
    $keys = $redis->keys('*item*');
    echo "Found " . count($keys) . " Redis keys with 'item':\n";

    foreach ($keys as $key) {
        echo "  - $key\n";
    }

    // Check for question set cache
    $qsKeys = $redis->keys('*question*');
    echo "\nFound " . count($qsKeys) . " Redis keys with 'question':\n";

    foreach ($qsKeys as $key) {
        echo "  - $key\n";
    }

    // Get some cache examples
    echo "\n=== SAMPLE CACHE CONTENTS ===\n";
    if (!empty($keys)) {
        $sampleKey = $keys[0];
        $value = $redis->get($sampleKey);
        echo "Sample cache ($sampleKey): " . substr($value, 0, 200) . "...\n";
    }

} catch (\Exception $e) {
    echo "❌ Redis error: " . $e->getMessage() . "\n";
}

echo "\n=== CHECKING LARAVEL CACHE ===\n";

// Check Laravel cache directly
$cacheItems = cache()->get('items', []);
echo "Items in Laravel cache: " . (is_array($cacheItems) ? count($cacheItems) : 'not an array') . "\n";

// Test fresh database query
echo "\n=== FRESH DATABASE QUERY ===\n";
$freshItems = Item::orderBy('id')->limit(5)->get();
echo "First 5 items from database:\n";
foreach ($freshItems as $item) {
    echo "  ID: {$item->id} - {$item->title}\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";