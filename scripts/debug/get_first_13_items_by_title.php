<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== 13 QUESTION SETS PERTAMA YANG AKAN DIHAPUS ===\n\n";

// Titles and types from screenshot in order (13 items)
$itemsFromScreenshot = [
    ['title' => 'Comprehensive Patient Management Plan', 'type' => 'essay', 'position' => 1],
    ['title' => 'Emergency Medicine: Trauma Management', 'type' => 'multiple-choice', 'position' => 2],
    ['title' => 'Diagnostic Imaging Interpretation', 'type' => 'essay', 'position' => 3],
    ['title' => 'Clinical Case: Cardiovascular Assessment', 'type' => 'multiple-choice', 'position' => 4],
    ['title' => 'Clinical Pharmacology: Drug Interactions', 'type' => 'multiple-choice', 'position' => 5],
    ['title' => 'Clinical Pharmacology: Drug Interactions', 'type' => 'multiple-choice', 'position' => 6],
    ['title' => 'Diagnostic Imaging Interpretation', 'type' => 'essay', 'position' => 7],
    ['title' => 'Comprehensive Patient Management Plan', 'type' => 'essay', 'position' => 8],
    ['title' => 'Emergency Medicine: Trauma Management', 'type' => 'multiple-choice', 'position' => 9],
    ['title' => 'Clinical Case: Cardiovascular Assessment', 'type' => 'multiple-choice', 'position' => 10],
    ['title' => 'Platform Feature: Rich Media Support', 'type' => 'multiple-choice', 'position' => 11],
    ['title' => 'Platform Feature: Interview Assessment', 'type' => 'interview', 'position' => 12],
    ['title' => 'Platform Feature: Multiple Choice', 'type' => 'multiple-choice', 'position' => 13],
];

$foundItems = [];
$usedIds = []; // Track which IDs we've already used

foreach ($itemsFromScreenshot as $searchItem) {
    $item = DB::table('items')
        ->where('title', $searchItem['title'])
        ->where('type', $searchItem['type'])
        ->whereNotIn('id', $usedIds) // Don't reuse same ID
        ->orderBy('id')
        ->first();
    
    if ($item) {
        $foundItems[] = [
            'position' => $searchItem['position'],
            'id' => $item->id,
            'title' => $item->title,
            'type' => $item->type,
        ];
        $usedIds[] = $item->id;
    } else {
        echo "⚠ Warning: Tidak ditemukan item untuk posisi {$searchItem['position']}: {$searchItem['title']} ({$searchItem['type']})\n";
    }
}

echo "\n" . str_repeat("=", 120) . "\n";
echo "LIST 13 QUESTION SETS YANG AKAN DIHAPUS:\n";
echo str_repeat("=", 120) . "\n\n";

printf("%-3s | %-5s | %-60s | %-20s\n", "NO", "ID", "TITLE", "TYPE");
echo str_repeat("-", 120) . "\n";

foreach ($foundItems as $item) {
    $title = $item['title'];
    if (strlen($title) > 60) {
        $title = substr($title, 0, 57) . '...';
    }
    
    // Get questions count
    $questionsCount = DB::table('questions')->where('item_id', $item['id'])->count();
    
    printf("%-3d | %-5d | %-60s | %-20s\n",
        $item['position'],
        $item['id'],
        $title,
        $item['type']
    );
}

echo str_repeat("=", 120) . "\n";

$ids = array_column($foundItems, 'id');
sort($ids);

echo "\nRingkasan:\n";
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

echo "\n" . str_repeat("=", 120) . "\n";
