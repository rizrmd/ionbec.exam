<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Exams\Item;

echo "=== LIST 13 QUESTION SETS YANG AKAN DIHAPUS ===\n\n";

// Get first 13 items (same order as shown in the UI)
$itemsToDelete = Item::orderBy('id')->limit(13)->get();

if ($itemsToDelete->isEmpty()) {
    echo "No items found in database.\n";
    exit(0);
}

echo "Berikut adalah 13 question sets pertama yang akan dihapus:\n\n";
echo str_repeat("=", 120) . "\n";
printf("%-3s | %-5s | %-55s | %-10s | %-20s\n", "NO", "ID", "TITLE", "QUESTIONS", "TYPE");
echo str_repeat("=", 120) . "\n";

foreach ($itemsToDelete as $index => $item) {
    $questionsCount = $item->questions->count();
    $typeName = $item->type->name ?? 'N/A';
    
    // Truncate title if too long
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

$totalQuestions = 0;
foreach ($itemsToDelete as $item) {
    $totalQuestions += $item->questions->count();
}

echo "\nRingkasan:\n";
echo "- Total Question Sets: " . $itemsToDelete->count() . "\n";
echo "- Total Questions: $totalQuestions\n";
echo "- IDs yang akan dihapus: " . $itemsToDelete->pluck('id')->implode(', ') . "\n";

echo "\n" . str_repeat("=", 120) . "\n";
