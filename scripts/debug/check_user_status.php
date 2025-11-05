<?php

// Database connection
$host = '107.155.75.50';
$port = 5986;
$dbname = 'ionbec-new';
$user = 'postgres';
$password = '6LP0Ojegy7IUU6kaX9lLkmZRUiAdAUNOltWyL3LegfYGR6rPQtB4DUSVqjdA78ES';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Checking user status and role assignment details...\n\n";

    // Check dwikora user details
    $username = 'dwikora@ionbec.com';
    $client_id = 3;

    // Get user details
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.username, u.email, u.client_id,
               u.email_verified_at, u.created_at, u.updated_at, u.deleted_at
        FROM users u
        WHERE u.username = ? AND u.client_id = ?
    ");
    $stmt->execute([$username, $client_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "USER DETAILS:\n";
        echo str_repeat("=", 50) . "\n";
        printf("ID: %d\n", $user['id']);
        printf("Name: %s\n", $user['name']);
        printf("Username: %s\n", $user['username']);
        printf("Email: %s\n", $user['email']);
        printf("Client ID: %d\n", $user['client_id']);
        printf("Email Verified: %s\n", $user['email_verified_at'] ? 'Yes' : 'No');
        printf("Created: %s\n", $user['created_at']);
        printf("Updated: %s\n", $user['updated_at']);
        printf("Deleted: %s\n", $user['deleted_at'] ? 'Yes' : 'No');
        echo "\n";

        // Check role assignments
        echo "ROLE ASSIGNMENTS:\n";
        echo str_repeat("=", 50) . "\n";
        $stmt = $pdo->prepare("
            SELECT r.id, r.name, r.slug, r.client_id as role_client_id,
                   ru.created_at as role_assigned_at
            FROM roles r
            JOIN role_user ru ON r.id = ru.role_id
            WHERE ru.user_id = ?
            ORDER BY r.id
        ");
        $stmt->execute([$user['id']]);
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($roles)) {
            foreach ($roles as $role) {
                printf("Role ID: %d\n", $role['id']);
                printf("Role Name: %s\n", $role['name']);
                printf("Role Slug: %s\n", $role['slug']);
                printf("Role Client ID: %s\n", $role['role_client_id'] ?: 'NULL (Global Role)');
                printf("Assigned At: %s\n", $role['role_assigned_at']);
                echo "\n";
            }
        } else {
            echo "❌ NO ROLES ASSIGNED\n\n";
        }

        // Check if there might be a status field in the users table
        echo "CHECKING FOR STATUS FIELDS:\n";
        echo str_repeat("=", 50) . "\n";
        $stmt = $pdo->prepare("
            SELECT column_name, data_type, is_nullable, column_default
            FROM information_schema.columns
            WHERE table_name = 'users'
            AND column_name ILIKE '%status%'
        ");
        $stmt->execute();
        $statusColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($statusColumns)) {
            foreach ($statusColumns as $column) {
                printf("Column: %s\n", $column['column_name']);
                printf("Type: %s\n", $column['data_type']);
                printf("Nullable: %s\n", $column['is_nullable']);
                printf("Default: %s\n\n", $column['column_default']);
            }
        } else {
            echo "No status-related columns found in users table\n\n";
        }

        // Check all columns in users table to see if there's a status field
        $stmt = $pdo->prepare("
            SELECT column_name, data_type
            FROM information_schema.columns
            WHERE table_name = 'users'
            ORDER BY ordinal_position
        ");
        $stmt->execute();
        $allColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "ALL USER TABLE COLUMNS:\n";
        echo str_repeat("=", 50) . "\n";
        foreach ($allColumns as $column) {
            printf("%-25s | %s\n", $column['column_name'], $column['data_type']);
        }

    } else {
        echo "❌ User not found: $username (client_id: $client_id)\n";
    }

} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}

?>