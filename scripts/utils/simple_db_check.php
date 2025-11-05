<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== SIMPLE DB CHECK ===\n";

try {
    // Cek total groups
    $totalGroups = DB::table('groups')->count();
    echo "Total groups: {$totalGroups}\n";

    // Cek 5 groups pertama
    $firstGroups = DB::table('groups')->limit(5)->get(['id', 'name', 'code']);
    echo "\nFirst 5 groups:\n";
    foreach ($firstGroups as $group) {
        echo "ID: {$group->id}, Name: {$group->name}, Code: {$group->code}\n";
    }

    // Cari dengan LIKE
    $likeGroups = DB::table('groups')
        ->where('name', 'like', '%BE%')
        ->limit(10)
        ->get(['id', 'name', 'code']);
    echo "\nGroups with 'BE' in name (first 10):\n";
    foreach ($likeGroups as $group) {
        echo "ID: {$group->id}, Name: {$group->name}, Code: {$group->code}\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}