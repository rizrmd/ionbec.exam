<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

// Check users with their roles using correct table name (no role column in users table)
echo "=== USERS WITH ROLES ===\n";
$usersWithRoles = DB::select("
    SELECT u.id, u.name, u.email, u.client_id, r.slug as role_slug, r.name as role_name
    FROM users u
    LEFT JOIN role_user ru ON u.id = ru.user_id
    LEFT JOIN roles r ON ru.role_id = r.id
    ORDER BY u.id LIMIT 10
");
foreach ($usersWithRoles as $user) {
    $roleInfo = $user->role_slug ? "Role: {$user->role_slug} ({$user->role_name})" : "No role";
    echo "User ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Client: {$user->client_id}, {$roleInfo}\n";
}

// Check role_user table structure
echo "\n=== ROLE_USER TABLE STRUCTURE ===\n";
$roleUserColumns = DB::select("
    SELECT column_name, data_type, is_nullable, column_default
    FROM information_schema.columns
    WHERE table_name = 'role_user'
    AND table_schema = 'public'
    ORDER BY ordinal_position
");
foreach ($roleUserColumns as $column) {
    echo sprintf(
        "%-20s %-15s %-8s %s\n",
        $column->column_name,
        $column->data_type,
        $column->is_nullable,
        $column->column_default ? 'DEFAULT: ' . $column->column_default : ''
    );
}

// Check all available roles
echo "\n=== ALL AVAILABLE ROLES ===\n";
$roles = DB::select("SELECT id, slug, name, description, client_id FROM roles ORDER BY client_id, slug");
foreach ($roles as $role) {
    $clientInfo = $role->client_id ? "Client ID: {$role->client_id}" : "Global";
    echo "ID: {$role->id}, Slug: {$role->slug}, Name: {$role->name}, Description: " . ($role->description ?? 'N/A') . " ({$clientInfo})\n";
}

// Check clients
echo "\n=== CLIENTS ===\n";
$clients = DB::select("SELECT id, name, slug FROM clients ORDER BY name");
foreach ($clients as $client) {
    echo "ID: {$client->id}, Name: {$client->name}, Slug: {$client->slug}\n";
}

// Check sample users in the system
echo "\n=== SAMPLE USERS ===\n";
$sampleUsers = DB::select("SELECT id, name, email, client_id, created_at FROM users ORDER BY id LIMIT 5");
foreach ($sampleUsers as $user) {
    echo "ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Client: {$user->client_id}, Created: {$user->created_at}\n";
}