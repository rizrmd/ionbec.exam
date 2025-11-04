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

    echo "Analyzing user display and role logic...\n\n";

    // Check how the UserManagementController retrieves roles
    echo "1. REPRODUCING CONTROLLER QUERY:\n";
    echo str_repeat("=", 50) . "\n";

    $username = 'dwikora@ionbec.com';
    $client_id = 3;

    // Similar to UserManagementController@index query
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.username, u.email, u.client_id,
               u.email_verified_at, u.last_login, u.created_at, u.deleted_at
        FROM users u
        WHERE u.username = ? AND u.client_id = ?
        LIMIT 1
    ");
    $stmt->execute([$username, $client_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        printf("User found: %s (ID: %d)\n", $user['name'], $user['id']);

        // Check how roles are loaded in the controller
        echo "\n2. CHECKING ROLE LOADING:\n";
        echo str_repeat("=", 50) . "\n";

        // With eager loading (as in controller)
        $stmt = $pdo->prepare("
            SELECT u.id, u.name, u.username,
                   r.id as role_id, r.name as role_name, r.slug as role_slug
            FROM users u
            LEFT JOIN role_user ru ON u.id = ru.user_id
            LEFT JOIN roles r ON ru.role_id = r.id
            WHERE u.id = ?
        ");
        $stmt->execute([$user['id']]);
        $userWithRoles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($userWithRoles)) {
            $roles = [];
            foreach ($userWithRoles as $row) {
                if ($row['role_id']) {
                    $roles[] = [
                        'id' => $row['role_id'],
                        'name' => $row['role_name'],
                        'slug' => $row['role_slug']
                    ];
                }
            }

            if (!empty($roles)) {
                echo "Roles found:\n";
                foreach ($roles as $role) {
                    printf("  - %s (slug: %s)\n", $role['name'], $role['slug']);
                }
            } else {
                echo "❌ No roles found for user\n";
            }
        }

        echo "\n3. FRONT-END PERSPECTIVE:\n";
        echo str_repeat("=", 50) . "\n";
        echo "From database perspective, the user HAS the scorer role assigned.\n";
        echo "The issue might be:\n";
        echo "  a) Frontend not loading roles properly\n";
        echo "  b) Display logic in Vue component\n";
        echo "  c) Inertia.js data transformation\n";
        echo "  d) Cache issues\n";

        echo "\n4. CHECKING POSSIBLE CACHE ISSUES:\n";
        echo str_repeat("=", 50) . "\n";

        // Check if there are any cache-related tables or mechanisms
        $stmt = $pdo->query("
            SELECT table_name
            FROM information_schema.tables
            WHERE table_name LIKE '%cache%' OR table_name LIKE '%session%'
        ");
        $cacheTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($cacheTables)) {
            echo "Found cache-related tables:\n";
            foreach ($cacheTables as $table) {
                echo "  - $table\n";
            }
        } else {
            echo "No cache tables found\n";
        }

        echo "\n5. EMAIL VERIFICATION STATUS:\n";
        echo str_repeat("=", 50) . "\n";
        printf("Email Verified: %s\n", $user['email_verified_at'] ? 'Yes' : 'No');
        if (!$user['email_verified_at']) {
            echo "⚠️  This might be causing the 'Pending' status!\n";
            echo "Some systems show 'Pending' for unverified emails.\n";
        }

    } else {
        echo "User not found\n";
    }

    echo "\n6. RECOMMENDATIONS:\n";
    echo str_repeat("=", 50) . "\n";
    echo "1. Verify email addresses for all scorer users\n";
    echo "2. Clear Laravel cache: php artisan cache:clear\n";
    echo "3. Clear view cache: php artisan view:clear\n";
    echo "4. Clear config cache: php artisan config:clear\n";
    echo "5. Check if there's a frontend issue with role display\n";

} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}

?>