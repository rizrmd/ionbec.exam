<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CEK DELIVERIES UNTUK GROUP BE 051125 ===\n\n";

try {
    // Cek deliveries untuk group BE 051125 (ID: 2)
    $deliveries = DB::table('deliveries')
        ->where('group_id', 2)
        ->get(['id', 'name', 'code', 'group_id']);

    echo "Group ID 2 (BE 051125) memiliki " . $deliveries->count() . " deliveries:\n\n";

    foreach ($deliveries as $delivery) {
        echo "Delivery ID: {$delivery->id}\n";
        echo "Name: {$delivery->name}\n";
        echo "Code: {$delivery->code}\n";
        echo "Group ID: {$delivery->group_id}\n\n";

        // Cek jika ada kesalahan penamaan di delivery code
        if (strpos($delivery->code, 'BE051125-BE') !== false) {
            $correctCode = str_replace('BE051125-BE ', 'BE 051125 - ', $delivery->code);
            echo "  -> PERBAIKAN CODE DARI: {$delivery->code}\n";
            echo "  -> MENJADI: {$correctCode}\n";

            DB::table('deliveries')
                ->where('id', $delivery->id)
                ->update(['code' => $correctCode]);

            echo "  -> BERHASIL DIPERBAIKI!\n\n";
        }
    }

    // Cari semua deliveries yang mungkin memiliki kode salah
    echo "=== CEK SEMUA DELIVERIES DENGAN KODE BE051125 ===\n\n";
    $wrongDeliveries = DB::table('deliveries')
        ->where('code', 'like', '%BE051125%')
        ->get(['id', 'name', 'code']);

    echo "Ditemukan " . $wrongDeliveries->count() . " deliveries dengan kode BE051125:\n\n";

    foreach ($wrongDeliveries as $delivery) {
        echo "Delivery ID: {$delivery->id}\n";
        echo "Name: {$delivery->name}\n";
        echo "Code: {$delivery->code}\n\n";

        // Perbaiki berbagai pattern kesalahan
        $needsFix = false;
        $correctCode = $delivery->code;

        if (strpos($delivery->code, 'BE051125-BE') !== false) {
            $correctCode = str_replace('BE051125-BE ', 'BE 051125 - ', $delivery->code);
            $needsFix = true;
        } elseif ($delivery->code === 'BE051125') {
            $correctCode = 'BE 051125';
            $needsFix = true;
        } elseif (strpos($delivery->code, 'BE051125-') !== false) {
            $correctCode = str_replace('BE051125-', 'BE 051125 - ', $delivery->code);
            $needsFix = true;
        }

        if ($needsFix) {
            echo "  MEMPERBAIKI: {$delivery->code} -> {$correctCode}\n";
            DB::table('deliveries')
                ->where('id', $delivery->id)
                ->update(['code' => $correctCode]);
            echo "  -> BERHASIL DIPERBAIKI!\n\n";
        }
    }

    echo "=== VERIFIKASI AKHIR ===\n";
    $finalDeliveries = DB::table('deliveries')
        ->where('code', 'like', '%BE051125%')
        ->orWhere('code', 'like', '%BE 051125%')
        ->get(['id', 'name', 'code']);

    echo "Setelah perbaikan, ditemukan " . $finalDeliveries->count() . " deliveries:\n\n";
    foreach ($finalDeliveries as $delivery) {
        echo "ID: {$delivery->id}, Name: {$delivery->name}, Code: {$delivery->code}\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}