<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Connect to database using Laravel's DB
use Illuminate\Support\Facades\DB;

echo "=== MEMPERBAIKI PENAMAAN CODE GROUP BE051125 ===\n\n";

try {
    // Cari semua group yang berhubungan dengan BE051125
    $allGroups = DB::table('groups')
        ->where('name', 'like', '%BE051125%')
        ->orWhere('code', 'like', '%BE051125%')
        ->get(['id', 'name', 'code']);

    echo "Ditemukan " . $allGroups->count() . " group yang berhubungan dengan BE051125:\n\n";

    foreach ($allGroups as $group) {
        echo "ID: {$group->id}\n";
        echo "Name: {$group->name}\n";
        echo "Code: {$group->code}\n\n";

        // Cek berbagai kemungkinan format yang salah
        $needsFix = false;
        $correctCode = $group->code;

        // Pattern 1: BE051125-BE 051125 - 01 -> BE 051125 - 01
        if (strpos($group->code, 'BE051125-BE') !== false) {
            $correctCode = str_replace('BE051125-BE ', 'BE 051125 - ', $group->code);
            $needsFix = true;
        }
        // Pattern 2: BE051125 -> BE 051125
        elseif ($group->code === 'BE051125') {
            $correctCode = 'BE 051125';
            $needsFix = true;
        }
        // Pattern 3: BE051125-01 -> BE 051125 - 01
        elseif (strpos($group->code, 'BE051125-') !== false) {
            $correctCode = str_replace('BE051125-', 'BE 051125 - ', $group->code);
            $needsFix = true;
        }

        if ($needsFix) {
            echo "  PERLU DIPERBAIKI:\n";
            echo "  Dari: {$group->code}\n";
            echo "  Menjadi: {$correctCode}\n";

            // Update kode
            DB::table('groups')
                ->where('id', $group->id)
                ->update(['code' => $correctCode]);

            echo "  -> BERHASIL DIPERBAIKI!\n\n";
        } else {
            echo "  -> Kode sudah benar.\n\n";
        }
    }

    echo "=== MEMPERBAIKI NAMA GROUP ===\n\n";

    // Perbaiki nama group juga jika perlu
    foreach ($allGroups as $group) {
        if ($group->name === 'BE051125') {
            echo "Memperbaiki nama group dari '{$group->name}' menjadi 'BE 051125'\n";

            DB::table('groups')
                ->where('id', $group->id)
                ->update(['name' => 'BE 051125']);

            echo "-> Nama group berhasil diperbaiki!\n\n";
        }
    }

    echo "=== VERIFIKASI DELIVERIES TERKAIT ===\n\n";

    // Tampilkan semua deliveries yang terkait
    foreach ($allGroups as $group) {
        $deliveries = DB::table('deliveries')
            ->where('group_id', $group->id)
            ->get(['id', 'name', 'group_id']);

        if ($deliveries->count() > 0) {
            echo "Group ID {$group->id} memiliki {$deliveries->count()} deliveries:\n";

            // Dapatkan nama group terbaru
            $updatedGroup = DB::table('groups')
                ->where('id', $group->id)
                ->first(['name', 'code']);

            foreach ($deliveries as $delivery) {
                echo "  - Delivery ID: {$delivery->id}, Name: {$delivery->name}\n";
            }
            echo "  Group Name: {$updatedGroup->name}, Code: {$updatedGroup->code}\n\n";
        }
    }

    echo "=== HASIL AKHIR ===\n";

    // Tampilkan hasil akhir semua group
    $finalGroups = DB::table('groups')
        ->where('name', 'like', '%BE051125%')
        ->orWhere('code', 'like', '%BE051125%')
        ->get(['id', 'name', 'code']);

    foreach ($finalGroups as $group) {
        echo "Final - ID: {$group->id}, Name: {$group->name}, Code: {$group->code}\n";
    }

    echo "\n=== PERBAIKAN SELESAI ===\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}