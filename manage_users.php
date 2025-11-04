<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// List of names to manage
$targetNames = [
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

// Function to generate username from name
function generateUsername($name) {
    // Remove titles and special characters
    $username = str_replace([
        'Prof. ', 'Dr. ', 'dr. ', 'SpOT(K)', ', ', '.', ' '
    ], ['', '', '', '', '', '', '_'], $name);

    // Convert to lowercase and remove multiple underscores
    $username = strtolower(preg_replace('/_+/', '_', $username));

    // Remove trailing underscore
    $username = rtrim($username, '_');

    return $username;
}

// Function to generate random password
function generatePassword($length = 12) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}

echo "MANAGING USERS FOR TARGET NAMES\n";
echo str_repeat("=", 80) . "\n";

$passwords = [];
$managedUsers = [];

foreach ($targetNames as $targetName) {
    echo "\nProcessing: {$targetName}\n";
    echo str_repeat("-", 50) . "\n";

    try {
        // Check if user exists by exact name match
        $existingUser = DB::table('users')->where('name', $targetName)->first();

        if ($existingUser) {
            echo "✓ Found existing user: {$targetName}\n";
            echo "  ID: {$existingUser->id}\n";
            echo "  Username: {$existingUser->username}\n";
            echo "  Email: {$existingUser->email}\n";

            // Generate new password
            $newPassword = generatePassword();
            $currentTime = date('Y-m-d H:i:s');

            // Update password
            DB::table('users')
                ->where('id', $existingUser->id)
                ->update([
                    'password' => Hash::make($newPassword),
                    'updated_at' => $currentTime
                ]);

            echo "  ✓ Updated password\n";
            echo "  New Password: {$newPassword}\n";

            $passwords[$existingUser->username] = $newPassword;
            $managedUsers[] = [
                'name' => $targetName,
                'username' => $existingUser->username,
                'email' => $existingUser->email,
                'password' => $newPassword,
                'status' => 'updated_existing'
            ];

        } else {
            echo "- User not found, checking partial matches...\n";

            // Try to find partial matches
            $partialMatch = DB::table('users')
                ->where('name', 'ILIKE', '%' . preg_replace('/\s*(,|\.|\().*$/', '', $targetName) . '%')
                ->first();

            if ($partialMatch) {
                echo "  Found potential match: {$partialMatch->name}\n";
                echo "  Username: {$partialMatch->username}\n";

                $newPassword = generatePassword();
                $currentTime = date('Y-m-d H:i:s');

                // Update password
                DB::table('users')
                    ->where('id', $partialMatch->id)
                    ->update([
                        'password' => Hash::make($newPassword),
                        'updated_at' => $currentTime
                    ]);

                echo "  ✓ Updated password\n";
                echo "  New Password: {$newPassword}\n";

                $passwords[$partialMatch->username] = $newPassword;
                $managedUsers[] = [
                    'name' => $targetName,
                    'username' => $partialMatch->username,
                    'email' => $partialMatch->email,
                    'password' => $newPassword,
                    'status' => 'updated_partial_match'
                ];

            } else {
                echo "  ✗ No match found, would create new user\n";
                echo "  (Manual creation required)\n";

                $managedUsers[] = [
                    'name' => $targetName,
                    'username' => 'N/A',
                    'email' => 'N/A',
                    'password' => 'N/A',
                    'status' => 'not_found'
                ];
            }
        }

    } catch (Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";
        $managedUsers[] = [
            'name' => $targetName,
            'username' => 'ERROR',
            'email' => 'ERROR',
            'password' => 'ERROR',
            'status' => 'error'
        ];
    }
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 80) . "\n";

$updatedCount = 0;
$notFoundCount = 0;
$errorCount = 0;

foreach ($managedUsers as $user) {
    switch ($user['status']) {
        case 'updated_existing':
        case 'updated_partial_match':
            $updatedCount++;
            break;
        case 'not_found':
            $notFoundCount++;
            break;
        case 'error':
            $errorCount++;
            break;
    }
}

echo "Total target users: " . count($targetNames) . "\n";
echo "Users updated: {$updatedCount}\n";
echo "Users not found: {$notFoundCount}\n";
echo "Errors: {$errorCount}\n";

echo "\n" . str_repeat("=", 80) . "\n";
echo "PASSWORD LIST\n";
echo str_repeat("=", 80) . "\n";

foreach ($passwords as $username => $password) {
    echo "{$username}: {$password}\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "ASSIGNING SCORER/COMMITTEE ROLES\n";
echo str_repeat("=", 80) . "\n";

$scorerRoleId = 2; // Scorer / Committee role

foreach ($passwords as $username => $password) {
    try {
        $user = DB::table('users')->where('username', $username)->first();
        if ($user) {
            // Check if user already has a role
            $existingRole = DB::table('role_user')
                ->where('user_id', $user->id)
                ->first();

            if (!$existingRole) {
                // Assign scorer role
                DB::table('role_user')->insert([
                    'user_id' => $user->id,
                    'role_id' => $scorerRoleId,
                    'user_type' => 'App\\Models\\User'
                ]);
                echo "✓ Assigned 'Scorer / Committee' role to {$username}\n";
            } else {
                $roleName = $existingRole->role_id == 1 ? 'Administrator' : 'Scorer / Committee';
                echo "- {$username} already has role: {$roleName}\n";
            }
        }
    } catch (Exception $e) {
        echo "✗ Error assigning role to {$username}: " . $e->getMessage() . "\n";
    }
}

echo "\nProcess completed!\n";