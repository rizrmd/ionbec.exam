<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "FINAL SUMMARY - USER MANAGEMENT\n";
echo str_repeat("=", 80) . "\n";

// Target names
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

echo "CHECKING EXACT MATCHES FOR TARGET NAMES\n";
echo str_repeat("-", 80) . "\n";

$foundUsers = [];
$notFoundUsers = [];

foreach ($targetNames as $targetName) {
    $user = DB::table('users')->where('name', $targetName)->first();

    if ($user) {
        $foundUsers[] = $user;
        echo "✓ FOUND: {$targetName}\n";
        echo "  ID: {$user->id}\n";
        echo "  Username: {$user->username}\n";
        echo "  Email: {$user->email}\n";
        echo "  Client ID: {$user->client_id}\n";
        echo "  Created: {$user->created_at}\n\n";
    } else {
        $notFoundUsers[] = $targetName;
        echo "✗ NOT FOUND: {$targetName}\n";
    }
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 80) . "\n";
echo "Total target names: " . count($targetNames) . "\n";
echo "Found in database: " . count($foundUsers) . "\n";
echo "Not found: " . count($notFoundUsers) . "\n";

echo "\n" . str_repeat("=", 80) . "\n";
echo "ROLES FOR FOUND USERS\n";
echo str_repeat("=", 80) . "\n";

foreach ($foundUsers as $user) {
    echo "\nUser: {$user->name}\n";
    echo "Username: {$user->username}\n";

    // Check user roles
    $roles = DB::table('role_user')
        ->join('roles', 'role_user.role_id', '=', 'roles.id')
        ->where('role_user.user_id', $user->id)
        ->select('roles.name', 'roles.id')
        ->get();

    if ($roles->count() > 0) {
        foreach ($roles as $role) {
            echo "  Role: {$role->name} (ID: {$role->id})\n";
        }
    } else {
        echo "  No roles assigned\n";
    }
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "ALL USERS WITH Scorer/Committee ROLE\n";
echo str_repeat("=", 80) . "\n";

$scorerUsers = DB::table('role_user')
    ->join('users', 'role_user.user_id', '=', 'users.id')
    ->join('roles', 'role_user.role_id', '=', 'roles.id')
    ->where('roles.name', 'Scorer / Committee')
    ->select('users.name', 'users.username', 'users.email', 'users.created_at')
    ->get();

echo "Total Scorer/Committee users: " . $scorerUsers->count() . "\n\n";

foreach ($scorerUsers as $user) {
    echo "Name: {$user->name}\n";
    echo "Username: {$user->username}\n";
    echo "Email: {$user->email}\n";
    echo "Created: {$user->created_at}\n";
    echo str_repeat("-", 40) . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "RECOMMENDATIONS\n";
echo str_repeat("=", 80) . "\n";

if (!empty($notFoundUsers)) {
    echo "\nUSERS THAT NEED TO BE CREATED:\n";
    echo str_repeat("-", 40) . "\n";
    foreach ($notFoundUsers as $name) {
        echo "- {$name}\n";
    }
    echo "\nThese users need to be manually created in the system with:\n";
    echo "1. Username (suggest: simplified name without titles)\n";
    echo "2. Email address\n";
    echo "3. Initial password\n";
    echo "4. Role: 'Scorer / Committee' (ID: 2)\n";
}

echo "\nEXISTING USERS WITH CORRECT ROLES:\n";
echo str_repeat("-", 40) . "\n";
foreach ($foundUsers as $user) {
    echo "- {$user->name} (Username: {$user->username})\n";
}

echo "\nProcess completed. Please review the above information and create the missing users manually if needed.\n";