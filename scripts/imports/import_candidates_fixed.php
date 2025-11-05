<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// List kandidat dari data yang diberikan
$candidates = [
    // WILAYAH WIB
    ['reg' => 'BE 051125 - 01', 'name' => 'dr. Febry Prayugo', 'email' => 'prayugo.corp@gmail.com'],
    ['reg' => 'BE 051125 - 02', 'name' => 'dr. Mohamad Almer Sahala', 'email' => 'almer.hutapea@yahoo.com'],
    ['reg' => 'BE 051125 - 03', 'name' => 'dr. Arham Adnani', 'email' => 'adnani.arham@gmail.com'],
    ['reg' => 'BE 051125 - 04', 'name' => 'dr. Hanif Fitriawan', 'email' => 'hanif.awang@gmail.com'],
    ['reg' => 'BE 051125 - 05', 'name' => 'dr. Muhammad Dimas Arya Candra Permana', 'email' => 'MDACP99@GMAIL.COM'],
    ['reg' => 'BE 051125 - 06', 'name' => 'dr. Rahadiyan Rheza Dewanto', 'email' => 'rahadiyanrheza@gmail.com'],
    ['reg' => 'BE 051125 - 07', 'name' => 'dr. Rizky Andrey Rarung', 'email' => 'rizkyrarung@gmail.com'],
    ['reg' => 'BE 051125 - 08', 'name' => 'dr. Rizwandha Noviar Azmi', 'email' => 'rizwandhanoviarazmi@gmail.com'],
    ['reg' => 'BE 051125 - 09', 'name' => 'dr. Tri Taufiqurachman Telaumbanua', 'email' => 'taufiq.rachman18@gmail.com'],
    ['reg' => 'BE 051125 - 10', 'name' => 'dr. Arie Kurniawan', 'email' => 'ariedr7@gmail.com'],
    ['reg' => 'BE 051125 - 11', 'name' => 'dr. Mahardika Frityatama', 'email' => 'mahardikaf@gmail.com'],
    ['reg' => 'BE 051125 - 12', 'name' => 'dr. Mohammad Muzakkiyafi', 'email' => 'Muzakiyafi@gmail.com'],
    ['reg' => 'BE 051125 - 13', 'name' => 'dr. M. Qathar RF Tulandi', 'email' => 'mqatharrefa@gmail.com'],
    ['reg' => 'BE 051125 - 14', 'name' => 'dr. Bernadeta Fuad Paramita Rahayu', 'email' => 'bernadeta.fuad.p@mail.ugm.ac.id'],
    ['reg' => 'BE 051125 - 15', 'name' => 'dr. Fuad Dheni Musthofa', 'email' => 'fuaddmusthofa@gmail.com'],
    ['reg' => 'BE 051125 - 16', 'name' => 'dr. Prisilla Desfiandi', 'email' => 'pdesfiandi@gmail.com'],
    ['reg' => 'BE 051125 - 17', 'name' => 'dr. Sharfan Anzhari', 'email' => 'sharfanzhr@gmail.com'],
    ['reg' => 'BE 051125 - 18', 'name' => 'dr. Shannen Karsten', 'email' => 'shannen_karsten@yahoo.com'],
    ['reg' => 'BE 051125 - 19', 'name' => 'dr. Ricovially Davya Guci', 'email' => 'davyarico@yahoo.com'],
    ['reg' => 'BE 051125 - 20', 'name' => 'dr. Tommy Mandagi', 'email' => 'tomymandagi.n@gmail.com'],
    ['reg' => 'BE 051125 - 21', 'name' => 'dr. Yudha Satria', 'email' => 'dr.yudhasatria@gmail.com'],
    ['reg' => 'BE 051125 - 22', 'name' => 'dr. Andryan Hanafi Bakri', 'email' => 'andryanh07@gmail.com'],
    ['reg' => 'BE 051125 - 23', 'name' => 'dr. Faiz Alam Rasyid', 'email' => 'faizalamrasyid@gmail.com'],
    ['reg' => 'BE 051125 - 24', 'name' => 'dr. William Putera Sukmajaya', 'email' => 'william.psky@gmail.com'],
    ['reg' => 'BE 051125 - 25', 'name' => 'dr. Handi Suntama Effendy', 'email' => 'hs_philos@hotmail.com'],
    ['reg' => 'BE 051125 - 26', 'name' => 'dr. Muhammad Randi Akbar', 'email' => 'mrandiakbar@gmail.com'],
    ['reg' => 'BE 051125 - 27', 'name' => 'dr. Satria Putra Wicaksana', 'email' => 'satriaputrawicaksana3@gmail.com'],
    ['reg' => 'BE 051125 - 28', 'name' => 'dr. Ghozi Natul Isral', 'email' => 'isralghozinatul@gmail.com'],
    ['reg' => 'BE 051125 - 29', 'name' => 'dr. Riko Febrian Kunta Adjie', 'email' => 'rikokuntaadjie@gmail.com'],

    // WILAYAH WITA
    ['reg' => 'BE 051125 - 30', 'name' => 'dr. Alsyahrin Manggala Putra Sarif', 'email' => 'alsyahrinp@gmail.com'],
    ['reg' => 'BE 051125 - 31', 'name' => 'dr. Ardian Mario', 'email' => 'brozzmario27@gmail.com'],
    ['reg' => 'BE 051125 - 32', 'name' => 'dr. Taufiq Akbar', 'email' => 'tafq.akbar@gmail.com'],
    ['reg' => 'BE 051125 - 33', 'name' => 'dr. Adiet Wahyu Kristian', 'email' => 'adietkristian@yahoo.co.id'],
    ['reg' => 'BE 051125 - 34', 'name' => 'dr. Anak Agung Ngurah Krisna Dwipayana', 'email' => 'krisnadwipayanaa@yahoo.com'],
    ['reg' => 'BE 051125 - 35', 'name' => 'dr. Gede Aditya Krisna', 'email' => 'aditkrisna19@gmail.com'],
    ['reg' => 'BE 051125 - 36', 'name' => 'dr. Ignatius Angga Rusdianto', 'email' => 'angga.rusdianto2704@gmail.com'],
    ['reg' => 'BE 051125 - 37', 'name' => 'dr. I Made Surya Budikusuma', 'email' => 'budikusuma1012@gmail.com'],
    ['reg' => 'BE 051125 - 38', 'name' => 'dr. Mikhail Kertajanottama Kushadiwijaya', 'email' => 'janottamakerta@gmail.com'],
    ['reg' => 'BE 051125 - 39', 'name' => 'dr. Sonia Daniati', 'email' => 'soniadaniati@gmail.com'],
];

echo "Memulai import " . count($candidates) . " kandidat...\n\n";

$successCount = 0;
$errorCount = 0;
$errors = [];

// Generate default password untuk semua kandidat
$defaultPassword = 'Ionbec2025!';
$hashedPassword = Hash::make($defaultPassword);

// Get current timestamp in proper format
$now = date('Y-m-d H:i:s');

foreach ($candidates as $index => $candidate) {
    try {
        // Check if email already exists
        $existing = DB::table('takers')->where('email', $candidate['email'])->first();
        if ($existing) {
            echo "⚠ Skip: {$candidate['reg']} - {$candidate['name']} - {$candidate['email']} (email already exists)\n";
            continue;
        }

        // Insert ke tabel takers
        $takerId = DB::table('takers')->insertGetId([
            'reg' => $candidate['reg'],
            'name' => $candidate['name'],
            'email' => strtolower($candidate['email']),
            'password' => $hashedPassword,
            'is_verified' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        echo "✓ Sukses: {$candidate['reg']} - {$candidate['name']} - {$candidate['email']} (ID: {$takerId})\n";
        $successCount++;
    } catch (\Exception $e) {
        $errorMsg = "✗ Error: {$candidate['reg']} - {$candidate['name']} - {$candidate['email']} | " . $e->getMessage();
        echo $errorMsg . "\n";
        $errors[] = $errorMsg;
        $errorCount++;
    }
}

echo "\n=== SUMMARY ===\n";
echo "Total kandidat: " . count($candidates) . "\n";
echo "Berhasil diimport: {$successCount}\n";
echo "Gagal: {$errorCount}\n";

if (!empty($errors)) {
    echo "\n=== DETAIL ERROR ===\n";
    foreach ($errors as $error) {
        echo $error . "\n";
    }
}

echo "\nPassword default untuk semua kandidat: {$defaultPassword}\n";
echo "Semua kandidat dibuat dengan status: belum terverifikasi\n";
echo "Email dibuat dalam format lowercase untuk konsistensi\n";