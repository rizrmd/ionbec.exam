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

    echo "Checking role loading issue in UserManagementController@index...\n\n";

    // Test the exact query that should be used in the controller
    echo "1. TESTING CONTROLLER QUERY WITH EAGER LOADING:\n";
    echo str_repeat("=", 60) . "\n";

    $client_id = 3;
    $search = 'dwikora';

    // This is how the controller should load users with roles
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.username, u.email, u.client_id,
               u.email_verified_at, u.created_at, u.updated_at, u.deleted_at
        FROM users u
        WHERE u.client_id = :client_id
        AND (u.name ILIKE :search OR u.email ILIKE :search)
        ORDER BY u.created_at DESC
        LIMIT 10
    ");

    $stmt->execute([
        'client_id' => $client_id,
        'search' => "%{$search}%"
    ]);

    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($users as $user) {
        echo "User: " . $user['name'] . " (ID: " . $user['id'] . ")\n";

        // Now load roles for this user
        $stmt = $pdo->prepare("
            SELECT r.id, r.name, r.slug
            FROM roles r
            JOIN role_user ru ON r.id = ru.role_id
            WHERE ru.user_id = :user_id
        ");
        $stmt->execute(['user_id' => $user['id']]);
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($roles)) {
            echo "Roles found:\n";
            foreach ($roles as $role) {
                echo "  - " . $role['name'] . " (slug: " . $role['slug'] . ")\n";
            }
        } else {
            echo "❌ No roles found for this user\n";
        }
        echo "\n";
    }

    echo "\n2. TESTING WITH FULL JOIN (what Inertia might see):\n";
    echo str_repeat("=", 60) . "\n";

    // Test what happens with a single query to see if there are data formatting issues
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.username, u.email, u.client_id,
               u.email_verified_at, u.created_at,
               COALESCE(
                 json_agg(
                   json_build_object(
                     'id', r.id,
                     'name', r.name,
                     'slug', r.slug
                   )
                 ) FILTER (WHERE r.id IS NOT NULL),
                 '[]'::json
               ) as roles
        FROM users u
        LEFT JOIN role_user ru ON u.id = ru.user_id
        LEFT JOIN roles r ON ru.role_id = r.id
        WHERE u.client_id = :client_id
        AND u.username ILIKE :username
        GROUP BY u.id, u.name, u.username, u.email, u.client_id, u.email_verified_at, u.created_at
    ");

    $stmt->execute([
        'client_id' => $client_id,
        'username' => '%dwikora%'
    ]);

    $userWithRoles = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userWithRoles) {
        echo "User: " . $userWithRoles['name'] . "\n";
        echo "Roles JSON: " . $userWithRoles['roles'] . "\n";

        // Decode to verify
        $roles = json_decode($userWithRoles['roles'], true);
        if (!empty($roles)) {
            echo "Decoded roles:\n";
            foreach ($roles as $role) {
                echo "  - ID: " . $role['id'] . ", Name: " . $role['name'] . ", Slug: " . $role['slug'] . "\n";
            }
        }
    }

    echo "\n3. DIAGNOSIS:\n";
    echo str_repeat("=", 60) . "\n";

    if ($userWithRoles && !empty(json_decode($userWithRoles['roles'], true))) {
        echo "✅ Database has the roles correctly assigned\n";
        echo "❌ Issue is likely in the Laravel controller or Inertia response\n";
        echo "\nPossible causes:\n";
        echo "  1. Controller not using eager loading: User::with('roles')\n";
        echo "  2. Global scopes filtering out roles\n";
        echo "  3. Inertia response not properly serializing relationships\n";
        echo "  4. Client-side caching issues\n";
    } else {
        echo "❌ Database issue - roles not properly assigned\n";
    }

} catch (PDOException $e) {
    echo "Database operation failed: " . $e->getMessage() . "\n";
}

?>