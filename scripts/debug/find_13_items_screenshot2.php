<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== MENCARI 13 QUESTION SETS DARI SCREENSHOT BARU ===\n\n";

// Titles and types from screenshot in order (13 items)
$itemsFromScreenshot = [
    ['title' => 'Diagnostic Imaging Interpretation', 'type' => 'essay', 'position' => 1],
    ['title' => 'Comprehensive Patient Management Plan', 'type' => 'essay', 'position' => 2],
    ['title' => 'Emergency Medicine: Trauma Management', 'type' => 'multiple-choice', 'position' => 3],
    ['title' => 'Clinical Case: Cardiovascular Assessment', 'type' => 'multiple-choice', 'position' => 4],
    ['title' => 'Clinical Pharmacology: Drug Interactions', 'type' => 'multiple-choice', 'position' => 5],
    ['title' => 'Platform Feature: Rich Media Support', 'type' => 'multiple-choice', 'position' => 6],
    ['title' => 'Platform Feature: Essay Questions', 'type' => 'essay', 'position' => 7],
    ['title' => 'Platform Feature: Interview Assessment', 'type' => 'interview', 'position' => 8],
    ['title' => 'Platform Feature: Multiple Choice', 'type' => 'multiple-choice', 'position' => 9],
    ['title' => 'Platform Feature: Complex Scenarios', 'type' => 'essay', 'position' => 10],
    ['title' => 'Platform Feature: Interview Assessment', 'type' => 'interview', 'position' => 11],
    ['title' => 'Platform Feature: Rich Media Support', 'type' => 'multiple-choice', 'position' => 12],
    ['title' => 'Platform Feature: Multiple Choice', 'type' => 'multiple-choice', 'position' => 13],
];

$foundItems = [];
$notFoundItems = [];
$usedIds = []; // Track which IDs we've already used

foreach ($itemsFromScreenshot as $searchItem) {
    $item = DB::table('items')
        ->where('title', $searchItem['title'])
        ->where('type', $searchItem['type'])
        ->whereNotIn('id', $usedIds) // Don't reuse same ID
        ->orderBy('id')
        ->first();
    
    if ($item) {
        // Count questions
        $questionsCount = DB::table('questions')->where('item_id', $item->id)->count();
        
        $foundItems[] = [
            'position' => $searchItem['position'],
            'id' => $item->id,
            'title' => $item->title,
            'type' => $item->type,
            'questions_count' => $questionsCount,
        ];
        $usedIds[] = $item->id;
    } else {
        $notFoundItems[] = [
            'position' => $searchItem['position'],
            'title' => $searchItem['title'],
            'type' => $searchItem['type'],
        ];
    }
}

echo str_repeat("=", 120) . "\n";
echo "HASIL PENCARIAN:\n";
echo str_repeat("=", 120) . "\n\n";

if (!empty($foundItems)) {
    echo "✅ Ditemukan " . count($foundItems) . " items yang cocok:\n\n";
    
    printf("%-3s | %-5s | %-60s | %-20s | %-10s\n", "NO", "ID", "TITLE", "TYPE", "QUESTIONS");
    echo str_repeat("-", 120) . "\n";
    
    foreach ($foundItems as $item) {
        $title = $item['title'];
        if (strlen($title) > 60) {
            $title = substr($title, 0, 57) . '...';
        }
        
        printf("%-3d | %-5d | %-60s | %-20s | %-10d\n",
            $item['position'],
            $item['id'],
            $title,
            $item['type'],
            $item['questions_count']
        );
    }
    
    echo str_repeat("=", 120) . "\n\n";
    
    $ids = array_column($foundItems, 'id');
    sort($ids);
    
    echo "Ringkasan:\n";
    echo "- Total items ditemukan: " . count($foundItems) . "\n";
    echo "- IDs yang akan dihapus: " . implode(', ', $ids) . "\n";
    
    // Show summary by type
    $typeCounts = [];
    foreach ($foundItems as $item) {
        if (!isset($typeCounts[$item['type']])) {
            $typeCounts[$item['type']] = 0;
        }
        $typeCounts[$item['type']]++;
    }
    
    echo "\nBreakdown by type:\n";
    foreach ($typeCounts as $type => $count) {
        echo "  - $type: $count items\n";
    }
    
} else {
    echo "❌ Tidak ada items yang ditemukan.\n";
}

if (!empty($notFoundItems)) {
    echo "\n⚠️  Items yang TIDAK ditemukan:\n";
    foreach ($notFoundItems as $item) {
        echo "  [{$item['position']}] {$item['title']} ({$item['type']})\n";
    }
}

echo "\n" . str_repeat("=", 120) . "\n";
