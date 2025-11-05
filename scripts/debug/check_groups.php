<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CEK GROUP DUPLIKASI ===\n";

// Cek semua group
$allGroups = DB::table('groups')->orderBy('id')->get();

echo "Semua groups yang ada:\n";
foreach ($allGroups as $group) {
    echo "ID: {$group->id} | Name: '{$group->name}' | Code: '{$group->code}' | Description: '{$group->description}'\n";
}

// Cek group dengan nama "BE051125"
$duplicateGroups = DB::table('groups')->where('name', 'BE051125')->get();

echo "\nGroup dengan nama 'BE051125':\n";
foreach ($duplicateGroups as $group) {
    echo "ID: {$group->id} | Name: '{$group->name}' | Code: '{$group->code}' | Description: '{$group->description}'\n";
}

// Cek group dengan code "BE051125"
$codeGroups = DB::table('groups')->where('code', 'BE051125')->get();

echo "\nGroup dengan code 'BE051125':\n";
foreach ($codeGroups as $group) {
    echo "ID: {$group->id} | Name: '{$group->name}' | Code: '{$group->code}' | Description: '{$group->description}'\n";
}

// Cek mana yang punya anggota
echo "\n=== CEK ANGGOTA GROUP ===\n";
foreach ($duplicateGroups as $group) {
    $memberCount = DB::table('group_taker')->where('group_id', $group->id)->count();
    echo "Group ID {$group->id} ('{$group->name}') memiliki {$memberCount} anggota\n";
}

// Cek struktur tabel groups
echo "\n=== STRUKTUR TABEL GROUPS ===\n";
$columns = DB::select("
    SELECT column_name, data_type, is_nullable, column_default
    FROM information_schema.columns
    WHERE table_name = 'groups'
    AND table_schema = 'public'
    ORDER BY ordinal_position
");

foreach ($columns as $column) {
    echo sprintf(
        "%-20s %-15s %-8s %s\n",
        $column->column_name,
        $column->data_type,
        $column->is_nullable,
        $column->column_default ? 'DEFAULT: ' . $column->column_default : ''
    );
}