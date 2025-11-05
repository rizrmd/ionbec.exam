<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

// Check takers table structure
echo "=== TAKERS TABLE STRUCTURE ===\n";
$takersColumns = DB::select("
    SELECT column_name, data_type, is_nullable, column_default, character_maximum_length
    FROM information_schema.columns
    WHERE table_name = 'takers'
    AND table_schema = 'public'
    ORDER BY ordinal_position
");
foreach ($takersColumns as $column) {
    echo sprintf(
        "%-20s %-20s %-8s %s\n",
        $column->column_name,
        $column->data_type . ($column->character_maximum_length ? "({$column->character_maximum_length})" : ""),
        $column->is_nullable,
        $column->column_default ? 'DEFAULT: ' . $column->column_default : ''
    );
}

// Check sample takers data
echo "\n=== SAMPLE TAKERS DATA ===\n";
$sampleTakers = DB::select("SELECT id, reg, name, email, is_verified, created_at FROM takers LIMIT 5");
foreach ($sampleTakers as $taker) {
    echo "ID: {$taker->id}, Reg: {$taker->reg}, Name: {$taker->name}, Email: {$taker->email}, Verified: " . ($taker->is_verified ? 'Yes' : 'No') . ", Created: {$taker->created_at}\n";
}

// Check group_taker table structure (pivot table)
echo "\n=== GROUP_TAKER TABLE STRUCTURE (PIVOT) ===\n";
$groupTakerColumns = DB::select("
    SELECT column_name, data_type, is_nullable, column_default
    FROM information_schema.columns
    WHERE table_name = 'group_taker'
    AND table_schema = 'public'
    ORDER BY ordinal_position
");
foreach ($groupTakerColumns as $column) {
    echo sprintf(
        "%-20s %-15s %-8s %s\n",
        $column->column_name,
        $column->data_type,
        $column->is_nullable,
        $column->column_default ? 'DEFAULT: ' . $column->column_default : ''
    );
}

// Check groups table structure
echo "\n=== GROUPS TABLE STRUCTURE ===\n";
$groupsColumns = DB::select("
    SELECT column_name, data_type, is_nullable, column_default
    FROM information_schema.columns
    WHERE table_name = 'groups'
    AND table_schema = 'public'
    ORDER BY ordinal_position
");
foreach ($groupsColumns as $column) {
    echo sprintf(
        "%-20s %-15s %-8s %s\n",
        $column->column_name,
        $column->data_type,
        $column->is_nullable,
        $column->column_default ? 'DEFAULT: ' . $column->column_default : ''
    );
}

// Check sample groups
echo "\n=== SAMPLE GROUPS ===\n";
$sampleGroups = DB::select("SELECT id, name, code, last_taker_code FROM groups LIMIT 5");
foreach ($sampleGroups as $group) {
    echo "ID: {$group->id}, Name: {$group->name}, Code: {$group->code}, Last Taker Code: {$group->last_taker_code}\n";
}