<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "=== ASSIGN KANDIDAT KE GROUP BE051125 (FIXED) ===\n\n";

// Cek apakah group "BE051125" sudah ada
$existingGroup = DB::table('groups')->where('name', 'BE051125')->first();

if ($existingGroup) {
    echo "✓ Group 'BE051125' sudah ada dengan ID: {$existingGroup->id}\n";
    $groupId = $existingGroup->id;
} else {
    echo "⚠ Group 'BE051125' belum ada, membuat group baru...\n";

    // Buat group baru
    $groupId = DB::table('groups')->insertGetId([
        'name' => 'BE051125',
        'description' => 'Group Kandidat Ujian BE 051125',
        'code' => 'BE051125',
        'last_taker_code' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ]);

    echo "✓ Group 'BE051125' berhasil dibuat dengan ID: {$groupId}\n";
}

// Ambil semua kandidat yang baru diimport (ID 2-40)
$candidateIds = range(2, 40);

echo "\nMemproses " . count($candidateIds) . " kandidat...\n";

$successCount = 0;
$skipCount = 0;

foreach ($candidateIds as $takerId) {
    // Cek apakah kandidat ada
    $taker = DB::table('takers')->where('id', $takerId)->first();
    if (!$taker) {
        echo "⚠ Skip: Taker ID {$takerId} tidak ditemukan\n";
        $skipCount++;
        continue;
    }

    // Cek apakah sudah ada di group BE051125
    $existingAssignment = DB::table('group_taker')
        ->where('taker_id', $takerId)
        ->where('group_id', $groupId)
        ->first();

    if ($existingAssignment) {
        echo "⚠ Skip: {$taker->name} (ID: {$takerId}) sudah ada di group BE051125\n";
        $skipCount++;
        continue;
    }

    // Assign ke group (tanpa created_at dan updated_at)
    try {
        DB::table('group_taker')->insert([
            'taker_id' => $takerId,
            'group_id' => $groupId,
            'taker_code' => $taker->reg, // Gunakan reg sebagai taker_code
        ]);

        echo "✓ Success: {$taker->name} (Reg: {$taker->reg}) ditambahkan ke group BE051125\n";
        $successCount++;
    } catch (\Exception $e) {
        echo "✗ Error: {$taker->name} - " . $e->getMessage() . "\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "Total kandidat diproses: " . count($candidateIds) . "\n";
echo "Berhasil diassign: {$successCount}\n";
echo "Dilewati (sudah ada/tidak ditemukan): {$skipCount}\n";

// Verifikasi final assignment
echo "\n=== VERIFIKASI FINAL ===\n";
$finalCount = DB::table('group_taker')
    ->where('group_id', $groupId)
    ->count();

echo "Total anggota group BE051125: {$finalCount}\n";

// Tampilkan beberapa anggota
$members = DB::table('group_taker')
    ->join('takers', 'group_taker.taker_id', '=', 'takers.id')
    ->where('group_taker.group_id', $groupId)
    ->select('takers.id', 'takers.reg', 'takers.name', 'takers.email', 'group_taker.taker_code')
    ->orderBy('takers.reg')
    ->limit(10) // Tampilkan 10 pertama saja
    ->get();

echo "\n10 anggota pertama group BE051125:\n";
foreach ($members as $member) {
    echo "ID: {$member->id} | Reg: {$member->reg} | Name: {$member->name} | Taker Code: {$member->taker_code}\n";
}

echo "\n✓ Selesai! Semua kandidat telah diassign ke group BE051125\n";

// Cek duplikasi data
echo "\n=== CEK DUPLIKASI DATA ===\n";
$duplicateEmails = DB::table('takers')
    ->select('email', DB::raw('COUNT(*) as count'))
    ->groupBy('email')
    ->having('count', '>', 1)
    ->get();

if (count($duplicateEmails) > 0) {
    echo "⚠ Ditemukan " . count($duplicateEmails) . " email duplikat:\n";
    foreach ($duplicateEmails as $dup) {
        echo "- {$dup->email} ({$dup->count} kali)\n";
    }
} else {
    echo "✓ Tidak ada email duplikat\n";
}

$duplicateRegs = DB::table('takers')
    ->select('reg', DB::raw('COUNT(*) as count'))
    ->where('reg', '!=', '')
    ->groupBy('reg')
    ->having('count', '>', 1)
    ->get();

if (count($duplicateRegs) > 0) {
    echo "⚠ Ditemukan " . count($duplicateRegs) . " nomor registrasi duplikat:\n";
    foreach ($duplicateRegs as $dup) {
        echo "- {$dup->reg} ({$dup->count} kali)\n";
    }
} else {
    echo "✓ Tidak ada nomor registrasi duplikat\n";
}