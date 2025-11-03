<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Exams\Item;

echo "=== MENCARI QUESTION SETS DENGAN TITLE DARI SCREENSHOT ===\n\n";

// Titles from screenshot (exact match)
$titlesFromScreenshot = [
    'Comprehensive Patient Management Plan',
    'Emergency Medicine: Trauma Management',
    'Diagnostic Imaging Interpretation',
    'Clinical Case: Cardiovascular Assessment',
    'Clinical Pharmacology: Drug Interactions',
    'Clinical Pharmacology: Drug Interactions', // duplicate in screenshot
    'Diagnostic Imaging Interpretation', // duplicate in screenshot
    'Comprehensive Patient Management Plan', // duplicate in screenshot
    'Emergency Medicine: Trauma Management', // duplicate in screenshot
    'Clinical Case: Cardiovascular Assessment', // duplicate in screenshot
    'Platform Feature: Rich Media Support',
    'Platform Feature: Interview Assessment',
    'Platform Feature: Multiple Choice',
];

// Remove duplicates and get unique titles
$uniqueTitles = array_unique($titlesFromScreenshot);

echo "Mencari " . count($uniqueTitles) . " unique titles dari screenshot...\n\n";

$foundItems = [];
$notFoundTitles = [];

foreach ($uniqueTitles as $title) {
    $items = Item::where('title', $title)->orderBy('id')->get();
    
    if ($items->isNotEmpty()) {
        foreach ($items as $item) {
            $foundItems[] = $item;
        }
        echo "✓ Found: \"$title\" - " . $items->count() . " item(s) found\n";
    } else {
        $notFoundTitles[] = $title;
        echo "✗ Not Found: \"$title\"\n";
    }
}

echo "\n" . str_repeat("=", 120) . "\n";
echo "HASIL PENCARIAN:\n";
echo str_repeat("=", 120) . "\n";

if (!empty($foundItems)) {
    echo "\nTotal items ditemukan: " . count($foundItems) . "\n\n";
    
    printf("%-3s | %-5s | %-55s | %-10s | %-20s\n", "NO", "ID", "TITLE", "QUESTIONS", "TYPE");
    echo str_repeat("-", 120) . "\n";
    
    foreach ($foundItems as $index => $item) {
        $questionsCount = $item->questions->count();
        $typeName = $item->type->name ?? 'N/A';
        
        $title = $item->title;
        if (strlen($title) > 55) {
            $title = substr($title, 0, 52) . '...';
        }
        
        printf("%-3d | %-5d | %-55s | %-10s | %-20s\n",
            $index + 1,
            $item->id,
            $title,
            $questionsCount . ' Question' . ($questionsCount > 1 ? 's' : ''),
            $typeName
        );
    }
    
    echo str_repeat("=", 120) . "\n";
    
    // Sort by ID to get the list in order
    usort($foundItems, function($a, $b) {
        return $a->id - $b->id;
    });
    
    $ids = array_map(function($item) { return $item->id; }, $foundItems);
    echo "\nIDs yang akan dihapus (sorted): " . implode(', ', $ids) . "\n";
    
} else {
    echo "\nTidak ada items yang ditemukan dengan title dari screenshot.\n";
}

if (!empty($notFoundTitles)) {
    echo "\n\nTitles yang tidak ditemukan:\n";
    foreach ($notFoundTitles as $title) {
        echo "- $title\n";
    }
}

echo "\n" . str_repeat("=", 120) . "\n";
