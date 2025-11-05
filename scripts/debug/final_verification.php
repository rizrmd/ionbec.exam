<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== FINAL VERIFICATION - PERBAIKAN PENAMAAN BE051125 ===\n\n";

try {
    // 1. Cek Group - sudah diperbaiki sebelumnya
    echo "1. GROUP TABLE:\n";
    $group = DB::table('groups')->where('id', 2)->first(['id', 'name', 'code', 'hash']);
    echo "   ID: {$group->id}\n";
    echo "   Name: {$group->name}\n";
    echo "   Code: {$group->code}\n";
    echo "   Hash: {$group->hash}\n";

    // Verifikasi sudah benar
    if ($group->name === 'BE 051125' && $group->code === 'BE 051125') {
        echo "   ✅ Group sudah BENAR\n\n";
    } else {
        echo "   ❌ Group masih SALAH\n\n";
    }

    // 2. Cek Deliveries
    echo "2. DELIVERIES TABLE:\n";
    $deliveries = DB::table('deliveries')
        ->where('group_id', 2)
        ->get(['id', 'name']);

    echo "   Group BE 051125 memiliki {$deliveries->count()} deliveries\n";

    // Cek semua deliveries yang mungkin memiliki nama salah
    $allWrongDeliveries = DB::table('deliveries')
        ->where('name', 'like', '%BE051125-BE%')
        ->get(['id', 'name']);

    echo "   Ditemukan {$allWrongDeliveries->count()} deliveries dengan nama salah\n";

    foreach ($allWrongDeliveries as $delivery) {
        $correctName = str_replace('BE051125-BE ', 'BE 051125 - ', $delivery->name);
        echo "   Memperbaiki: {$delivery->name} -> {$correctName}\n";

        DB::table('deliveries')
            ->where('id', $delivery->id)
            ->update(['name' => $correctName]);

        echo "   ✅ Berhasil diperbaiki\n";
    }

    // 3. Cek Takers (tanpa kolom code)
    echo "\n3. TAKERS TABLE:\n";
    $wrongTakers = DB::table('takers')
        ->where('name', 'like', '%BE051125-BE%')
        ->get(['id', 'name']);

    echo "   Ditemukan {$wrongTakers->count()} takers dengan nama salah\n";

    foreach ($wrongTakers as $taker) {
        $correctName = str_replace('BE051125-BE ', 'BE 051125 - ', $taker->name);
        echo "   Memperbaiki: {$taker->name} -> {$correctName}\n";

        DB::table('takers')
            ->where('id', $taker->id)
            ->update(['name' => $correctName]);

        echo "   ✅ Berhasil diperbaiki\n";
    }

    // 4. Cek Exams
    echo "\n4. EXAMS TABLE:\n";
    $wrongExams = DB::table('exams')
        ->where('name', 'like', '%BE051125-BE%')
        ->orWhere('code', 'like', '%BE051125-BE%')
        ->get(['id', 'name', 'code']);

    echo "   Ditemukan {$wrongExams->count()} exams dengan kode salah\n";

    foreach ($wrongExams as $exam) {
        $correctName = $exam->name;
        $correctCode = $exam->code;

        if (strpos($exam->name, 'BE051125-BE') !== false) {
            $correctName = str_replace('BE051125-BE ', 'BE 051125 - ', $exam->name);
        }
        if (strpos($exam->code, 'BE051125-BE') !== false) {
            $correctCode = str_replace('BE051125-BE ', 'BE 051125 - ', $exam->code);
        }

        echo "   Memperbaiki Exam ID {$exam->id}:\n";
        echo "   Name: {$exam->name} -> {$correctName}\n";
        echo "   Code: {$exam->code} -> {$correctCode}\n";

        DB::table('exams')
            ->where('id', $exam->id)
            ->update(['name' => $correctName, 'code' => $correctCode]);

        echo "   ✅ Berhasil diperbaiki\n\n";
    }

    // 5. Final Verification
    echo "5. FINAL VERIFICATION:\n";

    // Cek kembali group
    $finalGroup = DB::table('groups')->where('id', 2)->first(['name', 'code']);
    echo "   Group Name: {$finalGroup->name}\n";
    echo "   Group Code: {$finalGroup->code}\n";

    // Cek kembali deliveries
    $remainingWrongDeliveries = DB::table('deliveries')
        ->where('name', 'like', '%BE051125-BE%')
        ->count();

    // Cek kembali takers
    $remainingWrongTakers = DB::table('takers')
        ->where('name', 'like', '%BE051125-BE%')
        ->count();

    // Cek kembali exams
    $remainingWrongExams = DB::table('exams')
        ->where(function($query) {
            $query->where('name', 'like', '%BE051125-BE%')
                  ->orWhere('code', 'like', '%BE051125-BE%');
        })
        ->count();

    echo "\n   📊 SUMMARY:\n";
    echo "   ✅ Group: BE 051125 (sudah benar)\n";
    echo "   ✅ Deliveries dengan nama salah: {$remainingWrongDeliveries}\n";
    echo "   ✅ Takers dengan nama salah: {$remainingWrongTakers}\n";
    echo "   ✅ Exams dengan kode salah: {$remainingWrongExams}\n";

    if ($remainingWrongDeliveries == 0 && $remainingWrongTakers == 0 && $remainingWrongExams == 0) {
        echo "\n   🎉 SEMUA KESALAHAN PENAMAAN TELAH DIPERBAIKI!\n";
        echo "   🎯 Pattern 'BE051125-BE ' berhasil diubah menjadi 'BE 051125 - '\n";
    } else {
        echo "\n   ⚠️  Masih ada kesalahan yang perlu diperbaiki\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}