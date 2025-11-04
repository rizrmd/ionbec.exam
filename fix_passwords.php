<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIX PASSWORDS FOR ALL PENILAI IONBEC ===\n\n";

// First, let's get all users and manually match them
$allUsers = DB::table('users')->get();

echo "📊 Total users in database: " . $allUsers->count() . "\n\n";

// Define our target penilai with manual matching
$targetPenilai = [
    [
        'target_name' => 'Prof. Dr. dr. Dwikora November Utomo, SpOT(K)',
        'user_id' => 50, // Prof. Dr. Dwikora Novembri Utomo
        'password' => 'dwikora2024'
    ],
    [
        'target_name' => 'dr. Teddy Heri Wardhana, SpOT(K)',
        'user_id' => 29, // dr. Teddy H. Wardhana
        'password' => 'teddywardhana2024'
    ],
    [
        'target_name' => 'dr. Istan Irmansyah, SpOT(K)',
        'user_id' => 40, // Istan Irmansyah
        'password' => 'istanirmansyah2024'
    ],
    [
        'target_name' => 'Dr. dr. Muhammad Sakti, SpOT(K)',
        'user_id' => 4, // Dr. dr. Muhammad Sakti
        'password' => 'muhammadsakti2024'
    ],
    [
        'target_name' => 'Dr. dr. Mouli Edward, SpOT(K)',
        'user_id' => 5, // Dr. dr. Mouli Edward
        'password' => 'mouliedward2024'
    ],
    [
        'target_name' => 'dr. Syaifullah Asmiragani, SpOT(K)',
        'user_id' => 30, // dr. Syaifullah Asmiragani
        'password' => 'syaifullah2024'
    ],
    [
        'target_name' => 'Dr. dr. Mujaddid Idulhaq, SpOT(K)',
        'user_id' => 22, // dr. Mujaddid Idulhaq
        'password' => 'mujaddid2024'
    ],
    [
        'target_name' => 'Dr. dr. Ihsan Oesman, SpOT(K)',
        'user_id' => 36, // dr. Ihsan Oesman
        'password' => 'ihsanoesman2024'
    ],
    [
        'target_name' => 'Dr. dr. R. Andri Primadhi, SpOT(K)',
        'user_id' => 17, // Manual: Let's check if this exists
        'password' => 'andriprimadhi2024'
    ],
    [
        'target_name' => 'Dr. dr. Yudha Mathan Sakti, SpOT(K)',
        'user_id' => 34, // dr. Yudha Mathan Sakti
        'password' => 'yudhasakti2024'
    ],
    [
        'target_name' => 'dr. Pranajaya Dharma Kadar, SpOT(K)',
        'user_id' => 37, // dr. Pranajaya Dharma Kadar
        'password' => 'pranajaya2024'
    ],
    [
        'target_name' => 'Dr. dr. Rieva Ermawan, SpOT(K)',
        'user_id' => 19, // Manual: Let's check if this exists
        'password' => 'rievaermawan2024'
    ],
    [
        'target_name' => 'Dr. dr. I Gusti Ngurah Wien Aryana, SpOT(K)',
        'user_id' => 20, // Manual: Let's check if this exists
        'password' => 'wienaryana2024'
    ],
    [
        'target_name' => 'Dr. dr. Krisna Yuarno Phatama, SpOT(K)',
        'user_id' => 48, // dr. Krisna Yuarno
        'password' => 'krisnaphatama2024'
    ],
    [
        'target_name' => 'Dr. dr. Rendra Leonas, SpOT(K)',
        'user_id' => 43, // Dr. Rendra Leonas
        'password' => 'rendraleonas2024'
    ],
    [
        'target_name' => 'Dr. dr. Roni Eko Sahputra, SpOT(K)',
        'user_id' => 22, // Manual: Let's check if this exists
        'password' => 'ronisahputra2024'
    ],
    [
        'target_name' => 'Prof. Dr. dr. Azharuddin, SpOT(K)',
        'user_id' => 23, // Manual: Let's check if this exists
        'password' => 'azharuddin2024'
    ]
];

try {
    echo "🔄 Updating passwords for all penilai...\n\n";

    $credentials = [];
    $successCount = 0;
    $failCount = 0;

    foreach ($targetPenilai as $index => $penilai) {
        echo sprintf("%2d. Processing: %s\n", $index + 1, $penilai['target_name']);

        // Check if user exists
        $user = DB::table('users')->where('id', $penilai['user_id'])->first();

        if ($user) {
            // Update password
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'password' => Hash::make($penilai['password']),
                    'updated_at' => DB::raw('NOW()')
                ]);

            echo "    ✅ Found: {$user->name}\n";
            echo "    ✅ Email: {$user->email}\n";
            echo "    ✅ Username: {$user->username}\n";
            echo "    ✅ Password updated: {$penilai['password']}\n";

            $credentials[] = [
                'no' => $index + 1,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'password' => $penilai['password'],
                'target_name' => $penilai['target_name']
            ];

            $successCount++;
        } else {
            echo "    ❌ User ID {$penilai['user_id']} not found\n";

            // Try to find by name pattern
            $user = DB::table('users')
                ->where('name', 'LIKE', '%' . substr($penilai['target_name'], 0, 15) . '%')
                ->first();

            if ($user) {
                // Update password for found user
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'password' => Hash::make($penilai['password']),
                        'updated_at' => DB::raw('NOW()')
                    ]);

                echo "    ✅ Found by name: {$user->name}\n";
                echo "    ✅ Email: {$user->email}\n";
                echo "    ✅ Username: {$user->username}\n";
                echo "    ✅ Password updated: {$penilai['password']}\n";

                $credentials[] = [
                    'no' => $index + 1,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'password' => $penilai['password'],
                    'target_name' => $penilai['target_name']
                ];

                $successCount++;
            } else {
                echo "    ❌ Not found by name either\n";
                $failCount++;
            }
        }
        echo "\n";
    }

    echo "=== FINAL CREDENTIALS REPORT ===\n\n";

    echo "📋 LOGIN CREDENTIALS FOR PENILAI:\n";
    echo str_repeat("=", 120) . "\n";
    echo sprintf("%-4s %-45s %-35s %-20s %-20s\n",
        "NO", "NAMA LENGKAP", "EMAIL", "USERNAME", "PASSWORD");
    echo str_repeat("-", 120) . "\n";

    foreach ($credentials as $cred) {
        echo sprintf("%-4d %-45s %-35s %-20s %-20s\n",
            $cred['no'],
            substr($cred['name'], 0, 45),
            substr($cred['email'], 0, 35),
            substr($cred['username'], 0, 20),
            $cred['password']
        );
    }
    echo str_repeat("=", 120) . "\n\n";

    echo "📊 SUMMARY:\n";
    echo "Target penilai: " . count($targetPenilai) . "\n";
    echo "Successfully updated: " . $successCount . "\n";
    echo "Failed: " . $failCount . "\n\n";

    echo "🎯 LOGIN INFORMATION:\n";
    echo "URL: https://ionbec.com/login\n";
    echo "Role: Scorer (dapat melakukan penilaian ujian)\n\n";

    echo "📋 NEXT STEPS:\n";
    echo "1. Bagikan credentials ini ke masing-masing penilai\n";
    echo "2. Minta mereka untuk login dan ganti password pada kunjungan pertama\n";
    echo "3. Verifikasi semua penilai dapat mengakses sistem\n";
    echo "4. Test functionality scoring dengan akun penilai\n\n";

    // Generate improved CSV file
    echo "📁 Generating improved CSV file...\n";
    $csvContent = "NO,NAMA_LENGKAP,EMAIL,USERNAME,PASSWORD,TARGET_NAME\n";
    foreach ($credentials as $cred) {
        $csvContent .= $cred['no'] . ",";
        $csvContent .= '"' . str_replace('"', '""', $cred['name']) . '",';
        $csvContent .= $cred['email'] . ",";
        $csvContent .= $cred['username'] . ",";
        $csvContent .= $cred['password'] . ",";
        $csvContent .= '"' . str_replace('"', '""', $cred['target_name']) . '"' . "\n";
    }

    file_put_contents('penilai_credentials_fixed.csv', $csvContent);
    echo "✅ Improved CSV file created: penilai_credentials_fixed.csv\n\n";

    echo "✅ PASSWORD FIX COMPLETED!\n\n";

} catch (Exception $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}