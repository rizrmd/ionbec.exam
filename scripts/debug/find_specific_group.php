<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CARI GROUP DENGAN HASH 5bzO5NvE ===\n\n";

try {
    // Cari group dengan hash tertentu
    $group = DB::table('groups')
        ->where('hash', '5bzO5NvE')
        ->first(['id', 'name', 'code', 'hash']);

    if ($group) {
        echo "Group ditemukan:\n";
        echo "ID: {$group->id}\n";
        echo "Name: {$group->name}\n";
        echo "Code: {$group->code}\n";
        echo "Hash: {$group->hash}\n\n";

        // Cek deliveries untuk group ini
        $deliveries = DB::table('deliveries')
            ->where('group_id', $group->id)
            ->get(['id', 'name', 'hash', 'display_name']);

        echo "Group ini memiliki " . $deliveries->count() . " deliveries:\n\n";

        foreach ($deliveries as $delivery) {
            echo "Delivery ID: {$delivery->id}\n";
            echo "Name: {$delivery->name}\n";
            echo "Display Name: {$delivery->display_name}\n";
            echo "Hash: {$delivery->hash}\n\n";

            // Cek jika ada kesalahan penamaan
            if ($delivery->name && strpos($delivery->name, 'BE051125-BE') !== false) {
                $correctName = str_replace('BE051125-BE ', 'BE 051125 - ', $delivery->name);
                echo "  -> MEMPERBAIKI NAMA DARI: {$delivery->name}\n";
                echo "  -> MENJADI: {$correctName}\n";

                DB::table('deliveries')
                    ->where('id', $delivery->id)
                    ->update(['name' => $correctName]);

                echo "  -> BERHASIL DIPERBAIKI!\n\n";
            }

            if ($delivery->display_name && strpos($delivery->display_name, 'BE051125-BE') !== false) {
                $correctDisplayName = str_replace('BE051125-BE ', 'BE 051125 - ', $delivery->display_name);
                echo "  -> MEMPERBAIKI DISPLAY NAME DARI: {$delivery->display_name}\n";
                echo "  -> MENJADI: {$correctDisplayName}\n";

                DB::table('deliveries')
                    ->where('id', $delivery->id)
                    ->update(['display_name' => $correctDisplayName]);

                echo "  -> BERHASIL DIPERBAIKI!\n\n";
            }
        }

        // Perbaiki group code jika perlu
        if ($group->code && strpos($group->code, 'BE051125-BE') !== false) {
            $correctCode = str_replace('BE051125-BE ', 'BE 051125 - ', $group->code);
            echo "  -> MEMPERBAIKI GROUP CODE DARI: {$group->code}\n";
            echo "  -> MENJADI: {$correctCode}\n";

            DB::table('groups')
                ->where('id', $group->id)
                ->update(['code' => $correctCode]);

            echo "  -> BERHASIL DIPERBAIKI!\n\n";
        }

    } else {
        echo "Group dengan hash 5bzO5NvE tidak ditemukan!\n\n";

        // Cari semua group yang berhubungan dengan BE051125
        echo "=== SEMUA GROUP DENGAN BE051125 ===\n";
        $allGroups = DB::table('groups')
            ->where('name', 'like', '%BE051125%')
            ->orWhere('code', 'like', '%BE051125%')
            ->get(['id', 'name', 'code', 'hash']);

        foreach ($allGroups as $grp) {
            echo "ID: {$grp->id}, Name: {$grp->name}, Code: {$grp->code}, Hash: {$grp->hash}\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}