<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CEK HASIL PERBAIKAN GROUP BE051125 ===\n\n";

try {
    // Cek semua group yang berhubungan dengan BE051125
    $groups = DB::table('groups')
        ->where('name', 'like', '%BE051125%')
        ->orWhere('code', 'like', '%BE051125%')
        ->get(['id', 'name', 'code']);

    echo "Group yang ditemukan: " . $groups->count() . "\n\n";

    foreach ($groups as $group) {
        echo "Group ID: {$group->id}\n";
        echo "Name: {$group->name}\n";
        echo "Code: {$group->code}\n\n";
    }

    // Cek deliveries untuk group ini
    foreach ($groups as $group) {
        $deliveries = DB::table('deliveries')
            ->where('group_id', $group->id)
            ->get(['id', 'name']);

        echo "Group '{$group->name}' memiliki " . $deliveries->count() . " deliveries:\n";
        foreach ($deliveries as $delivery) {
            echo "  - {$delivery->name}\n";
        }
        echo "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}