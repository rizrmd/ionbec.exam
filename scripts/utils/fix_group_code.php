<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Connect to database using Laravel's DB
use Illuminate\Support\Facades\DB;

echo "=== MEMPERBAIKI KESALAHAN PENAMAAN CODE GROUP ===\n\n";

try {
    // Cari semua group dengan kode yang salah
    $wrongGroups = DB::table('groups')
        ->where('code', 'like', '%BE051125-BE%')
        ->orWhere('code', 'like', '%BE 051125%-%')
        ->get(['id', 'name', 'code']);

    echo "Ditemukan " . $wrongGroups->count() . " group dengan kode yang salah:\n";

    foreach ($wrongGroups as $group) {
        echo "ID: {$group->id}, Name: {$group->name}, Code: {$group->code}\n";

        // Perbaiki kode: hapus "BE051125-BE " menjadi "BE 051125 - "
        $correctCode = str_replace('BE051125-BE ', 'BE 051125 - ', $group->code);

        echo "  -> Memperbaiki ke: {$correctCode}\n";

        // Update kode
        DB::table('groups')
            ->where('id', $group->id)
            ->update(['code' => $correctCode]);

        echo "  -> Updated!\n\n";
    }

    echo "=== MEMPERBAIKI DELIVERIES TERKAIT ===\n\n";

    // Cari deliveries yang terkait dengan group tersebut
    foreach ($wrongGroups as $group) {
        $deliveries = DB::table('deliveries')
            ->where('group_id', $group->id)
            ->get(['id', 'name', 'group_id']);

        if ($deliveries->count() > 0) {
            echo "Group {$group->name} memiliki {$deliveries->count()} deliveries:\n";

            foreach ($deliveries as $delivery) {
                echo "  Delivery ID: {$delivery->id}, Name: {$delivery->name}\n";
            }
            echo "\n";
        }
    }

    echo "=== VERIFIKASI FINAL ===\n";

    // Tampilkan hasil akhir
    $finalGroups = DB::table('groups')
        ->where('name', 'like', '%BE051125%')
        ->orWhere('name', 'like', '%BE 051125%')
        ->get(['id', 'name', 'code']);

    foreach ($finalGroups as $group) {
        echo "Final - ID: {$group->id}, Name: {$group->name}, Code: {$group->code}\n";
    }

    echo "\n=== PERBAIKAN SELESAI ===\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}