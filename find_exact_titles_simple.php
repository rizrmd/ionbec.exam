<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== MENCARI QUESTION SETS DENGAN TITLE DARI SCREENSHOT ===\n\n";

// Titles from screenshot (exact match)
$titlesFromScreenshot = [
    'Comprehensive Patient Management Plan',
    'Emergency Medicine: Trauma Management',
    'Diagnostic Imaging Interpretation',
    'Clinical Case: Cardiovascular Assessment',
    'Clinical Pharmacology: Drug Interactions',
    'Platform Feature: Rich Media Support',
    'Platform Feature: Interview Assessment',
    'Platform Feature: Multiple Choice',
];

echo "Mencari items dengan title dari screenshot...\n\n";

$foundItems = [];

foreach ($titlesFromScreenshot as $title) {
    $items = DB::table('items')
        ->where('title', $title)
        ->orderBy('id')
        ->get();
    
    if ($items->isNotEmpty()) {
        foreach ($items as $item) {
            $foundItems[] = $item;
        }
        echo "✓ Found: \"$title\" - " . $items->count() . " item(s)\n";
    } else {
        echo "✗ Not Found: \"$title\"\n";
    }
}

echo "\n" . str_repeat("=", 120) . "\n";
echo "HASIL PENCARIAN:\n";
echo str_repeat("=", 120) . "\n";

if (!empty($foundItems)) {
    echo "\nTotal items ditemukan: " . count($foundItems) . "\n\n";
    
    printf("%-3s | %-5s | %-60s | %-15s\n", "NO", "ID", "TITLE", "TYPE");
    echo str_repeat("-", 120) . "\n";
    
    foreach ($foundItems as $index => $item) {
        // Count questions for this item
        $questionsCount = DB::table('questions')->where('item_id', $item->id)->count();
        
        $title = $item->title;
        if (strlen($title) > 60) {
            $title = substr($title, 0, 57) . '...';
        }
        
        printf("%-3d | %-5d | %-60s | %-15s\n",
            $index + 1,
            $item->id,
            $title,
            $item->type
        );
    }
    
    echo str_repeat("=", 120) . "\n";
    
    // Sort by ID
    usort($foundItems, function($a, $b) {
        return $a->id - $b->id;
    });
    
    $ids = array_map(function($item) { return $item->id; }, $foundItems);
    echo "\nIDs yang ditemukan (sorted): " . implode(', ', $ids) . "\n";
    echo "Total IDs: " . count($ids) . "\n";
    
} else {
    echo "\nTidak ada items yang ditemukan dengan title dari screenshot.\n";
}

echo "\n" . str_repeat("=", 120) . "\n";
