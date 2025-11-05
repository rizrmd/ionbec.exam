<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== VERIFIKASI PENGHAPUSAN ===\n\n";

// IDs that should be deleted
$deletedIds = [1769, 1771, 1772, 1804, 1805, 1806, 1807, 1808, 1809, 1810, 1811, 1812, 1813];

echo "Mengecek apakah IDs berikut telah dihapus: " . implode(', ', $deletedIds) . "\n\n";

// Check if any of these IDs still exist
$remainingItems = DB::table('items')->whereIn('id', $deletedIds)->get();

if ($remainingItems->isEmpty()) {
    echo "✅ Semua 13 items telah berhasil dihapus!\n\n";
} else {
    echo "⚠️  Masih ada " . $remainingItems->count() . " items yang belum terhapus:\n";
    foreach ($remainingItems as $item) {
        echo "  - ID: {$item->id} - {$item->title}\n";
    }
    echo "\n";
}

// Get total items remaining
$totalItems = DB::table('items')->count();
echo "Total question sets tersisa di database: $totalItems\n\n";

// Check if titles still exist
echo "Mengecek apakah title dari screenshot masih ada di database:\n\n";

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

foreach ($titlesFromScreenshot as $title) {
    $count = DB::table('items')->where('title', $title)->count();
    if ($count > 0) {
        echo "  ✓ \"$title\" - masih ada $count item(s)\n";
    } else {
        echo "  ✗ \"$title\" - tidak ada lagi\n";
    }
}

echo "\n" . str_repeat("=", 100) . "\n";
