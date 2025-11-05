<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== VERIFIKASI PENGHAPUSAN BATCH 4 ===\n\n";

// IDs that should be deleted
$deletedIds = [1768, 1785, 1790, 1793, 1794, 1795, 1798, 1799, 1800, 1801, 1802, 1803];

echo "Mengecek apakah IDs berikut telah dihapus: " . implode(', ', $deletedIds) . "\n\n";

// Check if any of these IDs still exist
$remainingItems = DB::table('items')->whereIn('id', $deletedIds)->get();

if ($remainingItems->isEmpty()) {
    echo "✅ Semua 12 items telah berhasil dihapus!\n\n";
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

// Get first 5 items to show what's now at the top
echo "5 Question Sets pertama sekarang:\n";
echo str_repeat("-", 100) . "\n";

$firstItems = DB::table('items')->orderBy('id')->limit(5)->get();
foreach ($firstItems as $index => $item) {
    $questionsCount = DB::table('questions')->where('item_id', $item->id)->count();
    echo sprintf("%d. ID: %-5d - %-60s (%s, %d Q)\n",
        $index + 1,
        $item->id,
        substr($item->title, 0, 60),
        $item->type,
        $questionsCount
    );
}

echo "\n" . str_repeat("=", 100) . "\n";

// Summary of all deletions
echo "\n📊 RINGKASAN TOTAL SEMUA PENGHAPUSAN:\n";
echo str_repeat("=", 100) . "\n";
echo "Batch 1: 13 items (IDs: 1769, 1771, 1772, 1804-1813)\n";
echo "Batch 2: 13 items (IDs: 1770, 1773, 1774, 1776, 1777, 1779, 1781, 1782, 1814-1818)\n";
echo "Batch 3: 13 items (IDs: 1775, 1778, 1780, 1783, 1784, 1786-1789, 1791, 1792, 1796, 1797)\n";
echo "Batch 4: 12 items (IDs: 1768, 1785, 1790, 1793-1795, 1798-1803)\n";
echo "\nTotal yang telah dihapus: 51 items\n";
echo "Database sekarang: $totalItems items\n";
echo str_repeat("=", 100) . "\n";
