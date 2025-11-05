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

    echo "Testing role fix by simulating Laravel query...\n\n";

    // Simulate the exact query that Laravel should execute
    $client_id = 3;
    $search = 'dwikora';

    echo "1. Testing User::with(['roles' => function(\$query) { \$query->withoutGlobalScopes(); }]) simulation:\n";
    echo str_repeat("=", 80) . "\n";

    // This is what Laravel should do with our fix
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.username, u.email, u.client_id, u.email_verified_at, u.created_at,
               r.id as role_id, r.name as role_name, r.slug as role_slug, r.client_id as role_client_id
        FROM users u
        LEFT JOIN role_user ru ON u.id = ru.user_id
        LEFT JOIN roles r ON ru.role_id = r.id
        WHERE u.client_id = :client_id
        AND (u.name ILIKE :search OR u.email ILIKE :search)
        ORDER BY u.created_at DESC
        LIMIT 10
    ");

    $stmt->execute([
        'client_id' => $client_id,
        'search' => "%{$search}%"
    ]);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $users = [];
    foreach ($results as $row) {
        $userId = $row['id'];

        if (!isset($users[$userId])) {
            $users[$userId] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'username' => $row['username'],
                'email' => $row['email'],
                'client_id' => $row['client_id'],
                'email_verified_at' => $row['email_verified_at'],
                'created_at' => $row['created_at'],
                'roles' => []
            ];
        }

        // Add role if exists
        if ($row['role_id']) {
            $users[$userId]['roles'][] = [
                'id' => $row['role_id'],
                'name' => $row['role_name'],
                'slug' => $row['role_slug']
            ];
        }
    }

    foreach ($users as $user) {
        echo "User: " . $user['name'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Status: " . ($user['email_verified_at'] ? 'Verified' : 'Pending') . "\n";

        if (!empty($user['roles'])) {
            echo "Roles:\n";
            foreach ($user['roles'] as $role) {
                echo "  - " . $role['name'] . " (" . $role['slug'] . ")\n";
            }
        } else {
            echo "Roles: ❌ No roles\n";
        }
        echo "\n";
    }

    echo "\n2. Testing direct relationship loading:\n";
    echo str_repeat("=", 80) . "\n";

    // Test if the relationship itself works
    $stmt = $pdo->prepare("
        SELECT u.id, u.name
        FROM users u
        WHERE u.username = :username AND u.client_id = :client_id
    ");
    $stmt->execute([
        'username' => 'dwikora@ionbec.com',
        'client_id' => $client_id
    ]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "Found user: " . $user['name'] . " (ID: " . $user['id'] . ")\n";

        // Load roles directly
        $stmt = $pdo->prepare("
            SELECT r.id, r.name, r.slug, r.client_id as role_client_id
            FROM roles r
            INNER JOIN role_user ru ON r.id = ru.role_id
            WHERE ru.user_id = :user_id
        ");
        $stmt->execute(['user_id' => $user['id']]);
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($roles)) {
            echo "Direct role query found:\n";
            foreach ($roles as $role) {
                echo "  - " . $role['name'] . " (ID: " . $role['id'] . ", Client: " . ($role['role_client_id'] ?: 'NULL') . ")\n";
            }
        } else {
            echo "❌ Direct role query found nothing!\n";
        }
    }

    echo "\n3. FINAL DIAGNOSIS:\n";
    echo str_repeat("=", 80) . "\n";

    if (!empty($users)) {
        $firstUser = reset($users);
        if (!empty($firstUser['roles'])) {
            echo "✅ DATABASE QUERY WORKS\n";
            echo "✅ Roles are available\n";
            echo "✅ The fix should work\n";
            echo "\n🔧 If roles still don't show in UI:\n";
            echo "  1. Force refresh browser (Ctrl+F5)\n";
            echo "  2. Check browser console for errors\n";
            echo "  3. Verify Inertia response includes roles data\n";
        } else {
            echo "❌ Database query shows no roles\n";
            echo "❌ There's still an issue with the relationship\n";
        }
    } else {
        echo "❌ No users found in query\n";
    }

} catch (PDOException $e) {
    echo "Database operation failed: " . $e->getMessage() . "\n";
}

?>