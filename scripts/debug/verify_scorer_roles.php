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

    echo "Connected to database successfully!\n\n";

    // List of usernames to verify
    $usernames = [
        'dwikora@ionbec.com',
        'teddy@ionbec.com',
        'istan@ionbec.com',
        'muhammad@ionbec.com',
        'mouli@ionbec.com',
        'syaifullah@ionbec.com',
        'mujaddid@ionbec.com',
        'ihsan@ionbec.com',
        'andri@ionbec.com',
        'yudha@ionbec.com',
        'pranajaya@ionbec.com',
        'rieva@ionbec.com',
        'gusti@ionbec.com',
        'krisna@ionbec.com',
        'rendra@ionbec.com',
        'roni@ionbec.com',
        'azharuddin@ionbec.com'
    ];

    echo "Verifying scorer roles for client_id=3:\n";
    echo str_repeat("=", 80) . "\n";

    $allHaveScorerRole = true;
    $userCount = 0;

    foreach ($usernames as $username) {
        $stmt = $pdo->prepare("
            SELECT u.id, u.name, u.username, u.client_id
            FROM users u
            WHERE u.username = ? AND u.client_id = 3
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $userCount++;

            // Get user roles
            $stmt = $pdo->prepare("
                SELECT r.id, r.name, r.slug
                FROM roles r
                JOIN role_user ru ON r.id = ru.role_id
                WHERE ru.user_id = ?
            ");
            $stmt->execute([$user['id']]);
            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $roleNames = [];
            $hasScorerRole = false;

            foreach ($roles as $role) {
                $roleNames[] = $role['name'] . ' (' . $role['slug'] . ')';
                if ($role['slug'] === 'scorer' || strtolower($role['name']) === 'scorer') {
                    $hasScorerRole = true;
                }
            }

            $rolesString = !empty($roleNames) ? implode(', ', $roleNames) : 'No roles assigned';

            if ($hasScorerRole) {
                $status = "✅ HAS SCORER ROLE";
            } else {
                $status = "❌ MISSING SCORER ROLE";
                $allHaveScorerRole = false;
            }

            printf("%-25s | %s\n", $username, $status);
            printf("  %-23s | Roles: %s\n", "", $rolesString);
            echo "\n";
        } else {
            printf("%-25s | ❌ USER NOT FOUND\n", $username);
            $allHaveScorerRole = false;
            echo "\n";
        }
    }

    echo str_repeat("=", 80) . "\n";
    echo "VERIFICATION SUMMARY:\n";
    printf("✅ Users found: %d/%d\n", $userCount, count($usernames));
    printf("🎯 All have scorer role: %s\n", $allHaveScorerRole ? "YES" : "NO");

    if (!$allHaveScorerRole) {
        echo "\n⚠️  Some users are missing the scorer role!\n";

        // Get scorer role ID
        $stmt = $pdo->prepare("SELECT id FROM roles WHERE slug = 'scorer'");
        $stmt->execute();
        $scorerRole = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($scorerRole) {
            echo "Scorer role ID: " . $scorerRole['id'] . "\n";
        }
    } else {
        echo "\n🎉 All users have been successfully assigned the scorer role!\n";
    }

} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}

?>