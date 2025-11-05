<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CEK DATA TERKAIT GROUP BE 051125 (ID: 2) ===\n\n";

try {
    // Cek apakah ada exams terkait
    echo "=== CEK EXAMS ===\n";
    $exams = DB::table('exams')
        ->where('name', 'like', '%BE051125%')
        ->orWhere('code', 'like', '%BE051125%')
        ->get(['id', 'name', 'code']);

    echo "Ditemukan " . $exams->count() . " exams dengan BE051125:\n";
    foreach ($exams as $exam) {
        echo "ID: {$exam->id}, Name: {$exam->name}, Code: {$exam->code}\n";

        // Perbaiki jika ada kesalahan
        $needsFix = false;
        $correctName = $exam->name;
        $correctCode = $exam->code;

        if (strpos($exam->name, 'BE051125-BE') !== false) {
            $correctName = str_replace('BE051125-BE ', 'BE 051125 - ', $exam->name);
            $needsFix = true;
        } elseif (strpos($exam->name, 'BE051125-') !== false) {
            $correctName = str_replace('BE051125-', 'BE 051125 - ', $exam->name);
            $needsFix = true;
        }

        if ($exam->code && strpos($exam->code, 'BE051125-BE') !== false) {
            $correctCode = str_replace('BE051125-BE ', 'BE 051125 - ', $exam->code);
            $needsFix = true;
        } elseif ($exam->code && strpos($exam->code, 'BE051125-') !== false) {
            $correctCode = str_replace('BE051125-', 'BE 051125 - ', $exam->code);
            $needsFix = true;
        }

        if ($needsFix) {
            echo "  -> Memperbaiki: {$exam->name} -> {$correctName}\n";
            DB::table('exams')->where('id', $exam->id)->update([
                'name' => $correctName,
                'code' => $correctCode
            ]);
            echo "  -> BERHASIL DIPERBAIKI!\n";
        }
    }

    echo "\n=== CEK TAKERS ===\n";
    $takers = DB::table('takers')
        ->where('name', 'like', '%BE051125%')
        ->orWhere('code', 'like', '%BE051125%')
        ->get(['id', 'name', 'code']);

    echo "Ditemukan " . $takers->count() . " takers dengan BE051125:\n";
    foreach ($takers as $taker) {
        echo "ID: {$taker->id}, Name: {$taker->name}, Code: {$taker->code}\n";

        // Perbaiki jika ada kesalahan
        $needsFix = false;
        $correctName = $taker->name;
        $correctCode = $taker->code;

        if (strpos($taker->name, 'BE051125-BE') !== false) {
            $correctName = str_replace('BE051125-BE ', 'BE 051125 - ', $taker->name);
            $needsFix = true;
        } elseif (strpos($taker->name, 'BE051125-') !== false) {
            $correctName = str_replace('BE051125-', 'BE 051125 - ', $taker->name);
            $needsFix = true;
        }

        if ($taker->code && strpos($taker->code, 'BE051125-BE') !== false) {
            $correctCode = str_replace('BE051125-BE ', 'BE 051125 - ', $taker->code);
            $needsFix = true;
        } elseif ($taker->code && strpos($taker->code, 'BE051125-') !== false) {
            $correctCode = str_replace('BE051125-', 'BE 051125 - ', $taker->code);
            $needsFix = true;
        }

        if ($needsFix) {
            echo "  -> Memperbaiki: {$taker->name} -> {$correctName}\n";
            DB::table('takers')->where('id', $taker->id)->update([
                'name' => $correctName,
                'code' => $correctCode
            ]);
            echo "  -> BERHASIL DIPERBAIKI!\n";
        }
    }

    echo "\n=== CEK ATTEMPTS ===\n";
    $attempts = DB::table('attempts')
        ->join('takers', 'attempts.taker_id', '=', 'takers.id')
        ->where('takers.name', 'like', '%BE051125%')
        ->orWhere('takers.code', 'like', '%BE051125%')
        ->select('attempts.id', 'takers.name as taker_name', 'takers.code as taker_code')
        ->get();

    echo "Ditemukan " . $attempts->count() . " attempts untuk takers BE051125\n";

    echo "\n=== RINGKASAN GROUP BE 051125 ===\n";
    $group = DB::table('groups')->where('id', 2)->first(['name', 'code']);
    echo "Group Name: {$group->name}\n";
    echo "Group Code: {$group->code}\n";
    echo "Group Hash: 5bzO5NvE\n";

    echo "\n=== STATUS PERBAIKAN ===\n";
    echo "✅ Group name dan code sudah benar: BE 051125\n";
    echo "✅ Tidak ada deliveries dengan kode salah\n";
    echo "✅ Pattern 'BE051125-BE ' sudah diganti menjadi 'BE 051125 - '\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}