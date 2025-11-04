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

echo "=== GENERATE PASSWORDS FOR PENILAI IONBEC ===\n\n";

// List of all 17 penilai names for matching
$penilaiNames = [
    'Prof. Dr. dr. Dwikora November Utomo, SpOT(K)',
    'dr. Teddy Heri Wardhana, SpOT(K)',
    'dr. Istan Irmansyah, SpOT(K)',
    'Dr. dr. Muhammad Sakti, SpOT(K)',
    'Dr. dr. Mouli Edward, SpOT(K)',
    'dr. Syaifullah Asmiragani, SpOT(K)',
    'Dr. dr. Mujaddid Idulhaq, SpOT(K)',
    'Dr. dr. Ihsan Oesman, SpOT(K)',
    'Dr. dr. R. Andri Primadhi, SpOT(K)',
    'Dr. dr. Yudha Mathan Sakti, SpOT(K)',
    'dr. Pranajaya Dharma Kadar, SpOT(K)',
    'Dr. dr. Rieva Ermawan, SpOT(K)',
    'Dr. dr. I Gusti Ngurah Wien Aryana, SpOT(K)',
    'Dr. dr. Krisna Yuarno Phatama, SpOT(K)',
    'Dr. dr. Rendra Leonas, SpOT(K)',
    'Dr. dr. Roni Eko Sahputra, SpOT(K)',
    'Prof. Dr. dr. Azharuddin, SpOT(K)'
];

try {
    echo "🔍 Processing penilai credentials...\n\n";

    $credentials = [];
    $errors = [];

    foreach ($penilaiNames as $index => $penilaiName) {
        echo sprintf("%2d. Processing: %s\n", $index + 1, $penilaiName);

        // Try to find user in database with various name matching strategies
        $user = null;

        // Strategy 1: Exact name match
        $user = DB::table('users')->where('name', $penilaiName)->first();

        // Strategy 2: Partial name match (remove titles and degrees)
        if (!$user) {
            $cleanName = str_replace([
                'Prof. ', 'Dr. ', 'dr. ', 'Prof', 'Dr', 'dr',
                ', SpOT(K)', ', Sp.OT', ', SpOT', ', Sp.'
            ], '', $penilaiName);
            $cleanName = trim($cleanName);

            $user = DB::table('users')
                ->where('name', 'LIKE', '%' . $cleanName . '%')
                ->orWhere('name', 'LIKE', '%' . substr($cleanName, 0, 10) . '%')
                ->first();
        }

        // Strategy 3: Name components matching
        if (!$user) {
            $nameParts = explode(' ', $penilaiName);
            $lastNames = array_slice($nameParts, -2); // Get last 2 name parts

            foreach ($lastNames as $lastName) {
                if (strlen($lastName) > 3) {
                    $user = DB::table('users')
                        ->where('name', 'LIKE', '%' . $lastName . '%')
                        ->first();
                    if ($user) break;
                }
            }
        }

        if ($user) {
            echo "    ✅ Found: {$user->name} ({$user->email})\n";

            // Generate simple, unique password
            $baseName = strtolower(str_replace([' ', '.', ','], '', $user->name));
            $baseName = preg_replace('/[^a-z0-9]/', '', $baseName);

            // Create unique password: name + number + simple pattern
            $password = $baseName . substr($user->id, -2) . '@2024';

            // Ensure password is not too long or too short
            if (strlen($password) > 20) {
                $password = substr($password, 0, 15) . '@2024';
            }
            if (strlen($password) < 8) {
                $password = $password . 'Ionbec@2024';
            }

            // Update password in database
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'password' => Hash::make($password),
                    'updated_at' => DB::raw('NOW()')
                ]);

            echo "    ✅ Password updated: {$password}\n";

            $credentials[] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'password' => $password,
                'original_target' => $penilaiName
            ];
        } else {
            echo "    ❌ User not found in database\n";
            $errors[] = [
                'name' => $penilaiName,
                'error' => 'User not found in database'
            ];
        }
        echo "\n";
    }

    echo "=== CREDENTIALS REPORT ===\n\n";

    if (!empty($credentials)) {
        echo "📋 LOGIN CREDENTIALS FOR PENILAI (" . count($credentials) . " users):\n";
        echo str_repeat("=", 100) . "\n";
        echo sprintf("%-4s %-35s %-30s %-20s %-20s\n",
            "NO", "NAMA LENGKAP", "EMAIL", "USERNAME", "PASSWORD");
        echo str_repeat("-", 100) . "\n";

        foreach ($credentials as $index => $cred) {
            echo sprintf("%-4d %-35s %-30s %-20s %-20s\n",
                $index + 1,
                substr($cred['name'], 0, 35),
                substr($cred['email'], 0, 30),
                substr($cred['username'], 0, 20),
                $cred['password']
            );
        }
        echo str_repeat("=", 100) . "\n\n";
    }

    echo "📊 SUMMARY:\n";
    echo "Total target penilai: " . count($penilaiNames) . "\n";
    echo "Credentials generated: " . count($credentials) . "\n";
    echo "Errors: " . count($errors) . "\n\n";

    if (!empty($errors)) {
        echo "❌ ERRORS:\n";
        echo str_repeat("=", 80) . "\n";
        foreach ($errors as $error) {
            echo "Name: {$error['name']}\n";
            echo "Error: {$error['error']}\n";
            echo str_repeat("-", 80) . "\n";
        }
        echo "\n";
    }

    echo "🎯 LOGIN INFORMATION:\n";
    echo "URL: https://ionbec.com/login\n";
    echo "Role: Scorer (dapat melakukan penilaian ujian)\n\n";

    echo "📋 NEXT STEPS:\n";
    echo "1. Bagikan credentials ini ke masing-masing penilai\n";
    echo "2. Minta mereka untuk login dan ganti password pada kunjungan pertama\n";
    echo "3. Verifikasi semua penilai dapat mengakses sistem\n";
    echo "4. Test functionality scoring dengan akun penilai\n\n";

    echo "✅ PASSWORD GENERATION COMPLETED!\n\n";

    // Generate CSV file for easy distribution
    echo "📁 Generating CSV file...\n";
    $csvContent = "NO,NAMA LENGKAP,EMAIL,USERNAME,PASSWORD\n";
    foreach ($credentials as $index => $cred) {
        $csvContent .= ($index + 1) . ",";
        $csvContent .= '"' . str_replace('"', '""', $cred['name']) . '",';
        $csvContent .= $cred['email'] . ",";
        $csvContent .= $cred['username'] . ",";
        $csvContent .= $cred['password'] . "\n";
    }

    file_put_contents('penilai_credentials.csv', $csvContent);
    echo "✅ CSV file created: penilai_credentials.csv\n\n";

} catch (Exception $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}