<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CEK STRUKTUR TABEL DELIVERIES ===\n\n";

try {
    // Cek struktur tabel deliveries
    $columns = DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'deliveries' ORDER BY ordinal_position");

    echo "Kolom-kolom dalam tabel deliveries:\n";
    foreach ($columns as $column) {
        echo "- {$column->column_name} ({$column->data_type})\n";
    }

    echo "\n=== CEK DELIVERIES UNTUK GROUP BE 051125 ===\n\n";

    // Cek deliveries untuk group ID 2 (BE 051125)
    $deliveries = DB::table('deliveries')
        ->where('group_id', 2)
        ->get(['id', 'name', 'group_id']);

    echo "Group ID 2 memiliki " . $deliveries->count() . " deliveries:\n\n";

    foreach ($deliveries as $delivery) {
        echo "Delivery ID: {$delivery->id}\n";
        echo "Name: {$delivery->name}\n";
        echo "Group ID: {$delivery->group_id}\n\n";

        // Cek jika nama delivery mengandung kode yang salah
        if (strpos($delivery->name, 'BE051125-BE') !== false) {
            $correctName = str_replace('BE051125-BE ', 'BE 051125 - ', $delivery->name);
            echo "  -> MEMPERBAIKI NAMA DARI: {$delivery->name}\n";
            echo "  -> MENJADI: {$correctName}\n";

            DB::table('deliveries')
                ->where('id', $delivery->id)
                ->update(['name' => $correctName]);

            echo "  -> BERHASIL DIPERBAIKI!\n\n";
        }
    }

    echo "=== CEK SEMUA DELIVERIES DENGAN NAMA BE051125 ===\n\n";
    $wrongDeliveries = DB::table('deliveries')
        ->where('name', 'like', '%BE051125%')
        ->get(['id', 'name', 'group_id']);

    echo "Ditemukan " . $wrongDeliveries->count() . " deliveries dengan nama BE051125:\n\n";

    foreach ($wrongDeliveries as $delivery) {
        echo "Delivery ID: {$delivery->id}\n";
        echo "Name: {$delivery->name}\n";
        echo "Group ID: {$delivery->group_id}\n\n";

        // Perbaiki berbagai pattern kesalahan
        $needsFix = false;
        $correctName = $delivery->name;

        if (strpos($delivery->name, 'BE051125-BE') !== false) {
            $correctName = str_replace('BE051125-BE ', 'BE 051125 - ', $delivery->name);
            $needsFix = true;
        } elseif ($delivery->name === 'BE051125') {
            $correctName = 'BE 051125';
            $needsFix = true;
        } elseif (strpos($delivery->name, 'BE051125-') !== false) {
            $correctName = str_replace('BE051125-', 'BE 051125 - ', $delivery->name);
            $needsFix = true;
        }

        if ($needsFix) {
            echo "  MEMPERBAIKI: {$delivery->name} -> {$correctName}\n";
            DB::table('deliveries')
                ->where('id', $delivery->id)
                ->update(['name' => $correctName]);
            echo "  -> BERHASIL DIPERBAIKI!\n\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}