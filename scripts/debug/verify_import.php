<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

// Verifikasi data yang baru diimport
echo "=== VERIFIKASI DATA IMPORT ===\n";

$newTakers = DB::table('takers')
    ->where('created_at', '>=', date('Y-m-d H:i:s', strtotime('-5 minutes')))
    ->orderBy('id')
    ->get();

echo "Total kandidat yang baru diimport: " . count($newTakers) . "\n\n";

foreach ($newTakers as $taker) {
    echo "ID: {$taker->id} | Reg: {$taker->reg} | Name: {$taker->name} | Email: {$taker->email} | Verified: " . ($taker->is_verified ? 'Yes' : 'No') . "\n";
}

// Cek total semua takers di database
$totalTakers = DB::table('takers')->count();
echo "\nTotal semua kandidat di database: {$totalTakers}\n";

// Cek kandidat yang sudah verified vs belum verified
$verifiedCount = DB::table('takers')->where('is_verified', true)->count();
$unverifiedCount = DB::table('takers')->where('is_verified', false)->count();

echo "Kandidat terverifikasi: {$verifiedCount}\n";
echo "Kandidat belum terverifikasi: {$unverifiedCount}\n";