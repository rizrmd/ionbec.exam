<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Names to create users for
$namesToCreate = [
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
    'Dr. dr. Rieva Ermawan, SpOT(K)',
    'Dr. dr. I Gusti Ngurah Wien Aryana, SpOT(K)',
    'Dr. dr. Krisna Yuarno Phatama, SpOT(K)',
    'Dr. dr. Rendra Leonas, SpOT(K)',
    'Dr. dr. Roni Eko Sahputra, SpOT(K)',
    'Prof. Dr. dr. Azharuddin, SpOT(K)'
];

// Check role structure
echo "Checking role structure...\n";
$roles = DB::table('roles')->get();
echo "Available roles:\n";
foreach ($roles as $role) {
    echo "- ID: {$role->id}, Name: {$role->name}\n";
}

echo "\n";

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

$createdUsers = [];
$passwords = [];

echo "Creating users...\n";
echo str_repeat("=", 80) . "\n";

foreach ($namesToCreate as $name) {
    try {
        $username = generateUsername($name);
        $password = generatePassword();

        // Check if username already exists
        $existingUser = DB::table('users')->where('username', $username)->first();
        if ($existingUser) {
            // Add number to username if it exists
            $counter = 1;
            do {
                $newUsername = $username . $counter;
                $existingUser = DB::table('users')->where('username', $newUsername)->first();
                $counter++;
            } while ($existingUser);
            $username = $newUsername;
        }

        // Create email based on username
        $email = $username . '@ionbec.com';

        // Insert user
        $userId = DB::table('users')->insertGetId([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password),
            'gender' => 'other',
            'created_at' => now(),
            'updated_at' => now(),
            'client_id' => 3 // Using same client_id as existing user
        ]);

        $createdUsers[] = [
            'id' => $userId,
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => $password
        ];

        $passwords[$username] = $password;

        echo "✓ Created user: {$name}\n";
        echo "  Username: {$username}\n";
        echo "  Email: {$email}\n";
        echo "  Password: {$password}\n";
        echo "  User ID: {$userId}\n\n";

    } catch (Exception $e) {
        echo "✗ Error creating user '{$name}': " . $e->getMessage() . "\n\n";
    }
}

echo str_repeat("=", 80) . "\n";
echo "SUMMARY:\n";
echo "Successfully created " . count($createdUsers) . " users\n\n";

// Also include the existing user
echo "Including existing user:\n";
$existingUser = DB::table('users')->where('name', 'dr. Pranajaya Dharma Kadar, SpOT(K)')->first();
if ($existingUser) {
    $newPassword = generatePassword();
    DB::table('users')
        ->where('id', $existingUser->id)
        ->update([
            'password' => Hash::make($newPassword),
            'updated_at' => now()
        ]);

    echo "- dr. Pranajaya Dharma Kadar, SpOT(K)\n";
    echo "  Username: {$existingUser->username}\n";
    echo "  Email: {$existingUser->email}\n";
    echo "  New Password: {$newPassword}\n\n";

    $passwords[$existingUser->username] = $newPassword;
}

echo "\nALL PASSWORDS:\n";
echo str_repeat("-", 50) . "\n";
foreach ($passwords as $username => $password) {
    echo "{$username}: {$password}\n";
}