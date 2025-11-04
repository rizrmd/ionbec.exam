<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// List of names to check
$names = [
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

echo "Checking user tables...\n";

// Get all tables first
try {
    $tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name LIKE '%user%' ORDER BY table_name");
    echo "Tables found:\n";
    foreach ($tables as $table) {
        echo "- " . $table->table_name . "\n";
    }
} catch (Exception $e) {
    echo "Error getting tables: " . $e->getMessage() . "\n";
}

// Try to find users table
$possibleTables = ['users', 'user_management', 'admin_users', 'backoffice_users'];
$usersTable = null;

foreach ($possibleTables as $table) {
    try {
        $count = DB::table($table)->count();
        echo "Found table '$table' with $count records\n";
        $usersTable = $table;
        break;
    } catch (Exception $e) {
        // Table doesn't exist, continue
    }
}

if (!$usersTable) {
    echo "No users table found. Trying to get all tables...\n";
    try {
        $allTables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
        echo "All tables in database:\n";
        foreach ($allTables as $table) {
            echo "- " . $table->table_name . "\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "\nChecking for the names in table: $usersTable\n";
    echo str_repeat("=", 80) . "\n";

    // Get table structure first
    try {
        $columns = DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = '$usersTable' ORDER BY ordinal_position");
        echo "Table structure:\n";
        foreach ($columns as $column) {
            echo "- " . $column->column_name . " (" . $column->data_type . ")\n";
        }
        echo "\n";
    } catch (Exception $e) {
        echo "Error getting table structure: " . $e->getMessage() . "\n";
    }

    $foundUsers = [];
    $notFoundUsers = [];

    foreach ($names as $name) {
        try {
            // Try different column names that might contain the user name
            $user = null;
            $nameColumns = ['name', 'full_name', 'username', 'display_name', 'email'];

            foreach ($nameColumns as $column) {
                try {
                    $user = DB::table($usersTable)->where($column, 'ILIKE', '%' . $name . '%')->first();
                    if ($user) {
                        echo "✓ Found: '$name' in column '$column'\n";
                        break;
                    }
                } catch (Exception $e) {
                    // Column might not exist
                }
            }

            if ($user) {
                $foundUsers[] = [
                    'name' => $name,
                    'user_data' => $user
                ];
            } else {
                echo "✗ Not found: '$name'\n";
                $notFoundUsers[] = $name;
            }
        } catch (Exception $e) {
            echo "Error checking '$name': " . $e->getMessage() . "\n";
            $notFoundUsers[] = $name;
        }
    }

    echo "\n" . str_repeat("=", 80) . "\n";
    echo "SUMMARY:\n";
    echo "Found users: " . count($foundUsers) . "\n";
    echo "Not found users: " . count($notFoundUsers) . "\n";

    if (!empty($foundUsers)) {
        echo "\nFound user details:\n";
        foreach ($foundUsers as $found) {
            echo "- " . $found['name'] . "\n";
            echo "  Database record: " . json_encode($found['user_data'], JSON_PRETTY_PRINT) . "\n\n";
        }
    }

    if (!empty($notFoundUsers)) {
        echo "\nUsers not found:\n";
        foreach ($notFoundUsers as $name) {
            echo "- " . $name . "\n";
        }
    }
}