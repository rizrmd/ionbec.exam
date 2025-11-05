<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

// Get users table structure
$columns = DB::select("
    SELECT
        column_name,
        data_type,
        is_nullable,
        column_default,
        character_maximum_length,
        numeric_precision,
        numeric_scale
    FROM information_schema.columns
    WHERE table_name = 'users'
    AND table_schema = 'public'
    ORDER BY ordinal_position
");

echo "=== USERS TABLE STRUCTURE ===\n";
foreach ($columns as $column) {
    echo sprintf(
        "%-20s %-15s %-8s %s\n",
        $column->column_name,
        $column->data_type,
        $column->is_nullable,
        $column->column_default ? 'DEFAULT: ' . $column->column_default : ''
    );
}

// Check other relevant tables
$tables = ['deliveries', 'exams', 'groups', 'clients', 'user_roles', 'roles'];

foreach ($tables as $table) {
    $exists = DB::select("SELECT EXISTS (
        SELECT FROM information_schema.tables
        WHERE table_schema = 'public'
        AND table_name = ?
    )", [$table]);

    if ($exists[0]->exists) {
        echo "\n=== $table TABLE STRUCTURE ===\n";
        $tableColumns = DB::select("
            SELECT column_name, data_type, is_nullable, column_default
            FROM information_schema.columns
            WHERE table_name = ?
            AND table_schema = 'public'
            ORDER BY ordinal_position
        ", [$table]);

        foreach ($tableColumns as $column) {
            echo sprintf(
                "%-20s %-15s %-8s %s\n",
                $column->column_name,
                $column->data_type,
                $column->is_nullable,
                $column->column_default ? 'DEFAULT: ' . $column->column_default : ''
            );
        }
    }
}

// Check relationships between users and other tables
echo "\n=== USER RELATIONSHIPS ===\n";
$relationships = DB::select("
    SELECT
        tc.table_name,
        kcu.column_name,
        ccu.table_name AS foreign_table_name,
        ccu.column_name AS foreign_column_name
    FROM information_schema.table_constraints AS tc
    JOIN information_schema.key_column_usage AS kcu
        ON tc.constraint_name = kcu.constraint_name
        AND tc.table_schema = kcu.table_schema
    JOIN information_schema.constraint_column_usage AS ccu
        ON ccu.constraint_name = tc.constraint_name
        AND ccu.table_schema = tc.table_schema
    WHERE tc.constraint_type = 'FOREIGN KEY'
    AND (tc.table_name = 'users' OR ccu.table_name = 'users')
");

foreach ($relationships as $rel) {
    if ($rel->table_name === 'users') {
        echo "users.{$rel->column_name} -> {$rel->foreign_table_name}.{$rel->foreign_column_name}\n";
    } else {
        echo "{$rel->table_name}.{$rel->column_name} -> users.{$rel->foreign_column_name}\n";
    }
}

// Check existing user roles
echo "\n=== EXISTING USER ROLES ===\n";
$roles = DB::select("SELECT DISTINCT role FROM users WHERE role IS NOT NULL");
foreach ($roles as $role) {
    echo "- {$role->role}\n";
}

// Sample user data to understand structure
echo "\n=== SAMPLE USER DATA ===\n";
$sampleUsers = DB::select("SELECT id, name, email, role, group_id, exam_id, client_id, created_at FROM users LIMIT 3");
foreach ($sampleUsers as $user) {
    echo "ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Role: {$user->role}, Group: {$user->group_id}, Exam: {$user->exam_id}, Client: {$user->client_id}\n";
}