<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

// Check all available roles
echo "=== ALL ROLES ===\n";
$roles = DB::select("SELECT id, slug, name, description, client_id FROM roles ORDER BY client_id, slug");
foreach ($roles as $role) {
    $clientInfo = $role->client_id ? "Client ID: {$role->client_id}" : "Global";
    echo "ID: {$role->id}, Slug: {$role->slug}, Name: {$role->name}, Description: " . ($role->description ?? 'N/A') . " ({$clientInfo})\n";
}

// Check if there are test taker specific roles
echo "\n=== TEST TAKER RELATED ROLES ===\n";
$testTakerRoles = DB::select("SELECT id, slug, name, description FROM roles WHERE slug ILIKE '%taker%' OR slug ILIKE '%test%' OR slug ILIKE '%candidate%' OR name ILIKE '%taker%' OR name ILIKE '%test%' OR name ILIKE '%candidate%' ORDER BY slug");
foreach ($testTakerRoles as $role) {
    echo "ID: {$role->id}, Slug: {$role->slug}, Name: {$role->name}, Description: " . ($role->description ?? 'N/A') . "\n";
}

// Check existing users with their roles
echo "\n=== SAMPLE USERS WITH ROLES ===\n";
$usersWithRoles = DB::select("
    SELECT u.id, u.name, u.email, u.client_id, r.slug as role_slug, r.name as role_name
    FROM users u
    LEFT JOIN user_roles ur ON u.id = ur.user_id
    LEFT JOIN roles r ON ur.role_id = r.id
    ORDER BY u.id LIMIT 10
");
foreach ($usersWithRoles as $user) {
    $roleInfo = $user->role_slug ? "Role: {$user->role_slug} ({$user->role_name})" : "No role";
    echo "User ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Client: {$user->client_id}, {$roleInfo}\n";
}

// Check clients
echo "\n=== CLIENTS ===\n";
$clients = DB::select("SELECT id, name, slug FROM clients ORDER BY name");
foreach ($clients as $client) {
    echo "ID: {$client->id}, Name: {$client->name}, Slug: {$client->slug}\n";
}