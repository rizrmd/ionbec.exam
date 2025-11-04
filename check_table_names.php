<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

// Get all table names
echo "=== ALL TABLES ===\n";
$tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
foreach ($tables as $table) {
    echo "- {$table->table_name}\n";
}

// Check for role-related tables
echo "\n=== ROLE-RELATED TABLES ===\n";
$roleTables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name LIKE '%role%' ORDER BY table_name");
foreach ($roleTables as $table) {
    echo "- {$table->table_name}\n";
}

// Check user-related tables
echo "\n=== USER-RELATED TABLES ===\n";
$userTables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name LIKE '%user%' ORDER BY table_name");
foreach ($userTables as $table) {
    echo "- {$table->table_name}\n";
}