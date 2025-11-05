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

    // List of usernames to check
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

    echo "Checking existing users for client_id=3:\n";
    echo str_repeat("=", 50) . "\n";

    $existingUsers = [];
    $missingUsers = [];

    foreach ($usernames as $username) {
        $stmt = $pdo->prepare("SELECT id, username, name, email FROM users WHERE username = ? AND client_id = 3");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $existingUsers[] = $user;
            printf("✅ FOUND: %s\n", $username);
            printf("   ID: %d, Name: %s\n", $user['id'], $user['name']);
        } else {
            $missingUsers[] = $username;
            printf("❌ MISSING: %s\n", $username);
        }
    }

    echo "\n" . str_repeat("=", 50) . "\n";
    printf("SUMMARY:\n");
    printf("Existing users: %d\n", count($existingUsers));
    printf("Missing users: %d\n", count($missingUsers));

    if (!empty($missingUsers)) {
        echo "\nUsers that need to be created:\n";
        foreach ($missingUsers as $username) {
            echo "- $username\n";
        }
    }

} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}

?>