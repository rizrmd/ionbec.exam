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

    echo "Fixing email verification for scorer users...\n\n";

    // List of all scorer usernames
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

    $client_id = 3;
    $updatedCount = 0;

    echo "Marking emails as verified for client_id=$client_id:\n";
    echo str_repeat("=", 60) . "\n";

    $pdo->beginTransaction();

    try {
        foreach ($usernames as $username) {
            // Check current status
            $stmt = $pdo->prepare("
                SELECT id, name, email_verified_at
                FROM users
                WHERE username = ? AND client_id = ?
            ");
            $stmt->execute([$username, $client_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                if (!$user['email_verified_at']) {
                    // Update email_verified_at to current timestamp
                    $stmt = $pdo->prepare("
                        UPDATE users
                        SET email_verified_at = NOW(),
                            updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([$user['id']]);
                    $updatedCount++;

                    printf("✅ UPDATED: %s\n", $username);
                    printf("   Name: %s\n", $user['name']);
                    printf("   Status: Email now verified\n\n");
                } else {
                    printf("⏭️  SKIPPED: %s (already verified)\n", $username);
                    printf("   Name: %s\n\n", $user['name']);
                }
            } else {
                printf("❌ NOT FOUND: %s\n\n", $username);
            }
        }

        $pdo->commit();

        echo str_repeat("=", 60) . "\n";
        echo "EMAIL VERIFICATION SUMMARY:\n";
        printf("✅ Users updated: %d\n", $updatedCount);
        printf("📊 Total processed: %d\n", count($usernames));

        if ($updatedCount > 0) {
            echo "\n🎉 Email verification fixed!\n";
            echo "Next step: Clear Laravel caches on the server:\n";
            echo "  php artisan cache:clear\n";
            echo "  php artisan view:clear\n";
            echo "  php artisan config:clear\n";
        }

        // Verify a few users
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "VERIFICATION OF UPDATED USERS:\n";
        $testUsers = ['dwikora@ionbec.com', 'teddy@ionbec.com'];

        foreach ($testUsers as $username) {
            $stmt = $pdo->prepare("
                SELECT u.name, u.email_verified_at,
                       r.name as role_name, r.slug as role_slug
                FROM users u
                LEFT JOIN role_user ru ON u.id = ru.user_id
                LEFT JOIN roles r ON ru.role_id = r.id
                WHERE u.username = ? AND u.client_id = ?
            ");
            $stmt->execute([$username, $client_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                printf("\n%s:\n", $username);
                printf("  Name: %s\n", $result['name']);
                printf("  Email Verified: %s\n", $result['email_verified_at'] ? '✅ Yes' : '❌ No');
                printf("  Role: %s (%s)\n", $result['role_name'], $result['role_slug']);
            }
        }

    } catch (Exception $e) {
        $pdo->rollback();
        throw $e;
    }

} catch (PDOException $e) {
    echo "Database operation failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>