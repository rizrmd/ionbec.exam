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

    // Scorer data
    $scorers = [
        ['name' => 'Prof. Dr. dr. Dwikora November Utomo, SpOT(K)', 'username' => 'dwikora@ionbec.com', 'password' => 'dwikora2025'],
        ['name' => 'dr. Teddy Heri Wardhana, SpOT(K)', 'username' => 'teddy@ionbec.com', 'password' => 'teddy2025'],
        ['name' => 'dr. Istan Irmansyah, SpOT(K)', 'username' => 'istan@ionbec.com', 'password' => 'istan2025'],
        ['name' => 'Dr. dr. Muhammad Sakti, SpOT(K)', 'username' => 'muhammad@ionbec.com', 'password' => 'muhammad2025'],
        ['name' => 'Dr. dr. Mouli Edward, SpOT(K)', 'username' => 'mouli@ionbec.com', 'password' => 'mouli2025'],
        ['name' => 'dr. Syaifullah Asmiragani, SpOT(K)', 'username' => 'syaifullah@ionbec.com', 'password' => 'syaifullah2025'],
        ['name' => 'Dr. dr. Mujaddid Idulhaq, SpOT(K)', 'username' => 'mujaddid@ionbec.com', 'password' => 'mujaddid2025'],
        ['name' => 'Dr. dr. Ihsan Oesman, SpOT(K)', 'username' => 'ihsan@ionbec.com', 'password' => 'ihsan2025'],
        ['name' => 'Dr. dr. R. Andri Primadhi, SpOT(K)', 'username' => 'andri@ionbec.com', 'password' => 'andri2025'],
        ['name' => 'Dr. dr. Yudha Mathan Sakti, SpOT(K)', 'username' => 'yudha@ionbec.com', 'password' => 'yudha2025'],
        ['name' => 'dr. Pranajaya Dharma Kadar, SpOT(K)', 'username' => 'pranajaya@ionbec.com', 'password' => 'pranajaya2025'],
        ['name' => 'Dr. dr. Rieva Ermawan, SpOT(K)', 'username' => 'rieva@ionbec.com', 'password' => 'rieva2025'],
        ['name' => 'Dr. dr. I Gusti Ngurah Wien Aryana, SpOT(K)', 'username' => 'gusti@ionbec.com', 'password' => 'gusti2025'],
        ['name' => 'Dr. dr. Krisna Yuarno Phatama, SpOT(K)', 'username' => 'krisna@ionbec.com', 'password' => 'krisna2025'],
        ['name' => 'Dr. dr. Rendra Leonas, SpOT(K)', 'username' => 'rendra@ionbec.com', 'password' => 'rendra2025'],
        ['name' => 'Dr. dr. Roni Eko Sahputra, SpOT(K)', 'username' => 'roni@ionbec.com', 'password' => 'roni2025'],
        ['name' => 'Prof. Dr. dr. Azharuddin, SpOT(K)', 'username' => 'azharuddin@ionbec.com', 'password' => 'azharuddin2025']
    ];

    $client_id = 3;
    $scorer_role_id = 2; // From previous check

    echo "Creating/Updating scorer users for client_id=$client_id:\n";
    echo str_repeat("=", 60) . "\n";

    $createdUsers = [];
    $updatedUsers = [];

    $pdo->beginTransaction();

    try {
        foreach ($scorers as $index => $scorer) {
            $name = $scorer['name'];
            $username = $scorer['username'];
            $email = $scorer['username']; // Use username as email
            $password = password_hash($scorer['password'], PASSWORD_DEFAULT);

            // Check if user exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND client_id = ?");
            $stmt->execute([$username, $client_id]);
            $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingUser) {
                // Update existing user
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
                $stmt->execute([$name, $email, $password, $existingUser['id']]);
                $userId = $existingUser['id'];
                $updatedUsers[] = $scorer;
                printf("✅ UPDATED: %s (ID: %d)\n", $username, $userId);
            } else {
                // Create new user
                $stmt = $pdo->prepare("
                    INSERT INTO users (name, username, email, password, client_id, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                    RETURNING id
                ");
                $stmt->execute([$name, $username, $email, $password, $client_id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $userId = $result['id'];
                $createdUsers[] = $scorer;
                printf("🆕 CREATED: %s (ID: %d)\n", $username, $userId);
            }

            // Assign scorer role (remove existing assignments first)
            $stmt = $pdo->prepare("DELETE FROM role_user WHERE user_id = ?");
            $stmt->execute([$userId]);

            $stmt = $pdo->prepare("INSERT INTO role_user (user_id, role_id) VALUES (?, ?)");
            $stmt->execute([$userId, $scorer_role_id]);

            printf("   📋 Name: %s\n", $name);
            printf("   🔐 Password: %s\n", $scorer['password']);
            printf("   👤 Role assigned: Scorer\n");
            printf("   📧 Email: %s\n", $email);
            echo "\n";
        }

        $pdo->commit();

        echo str_repeat("=", 60) . "\n";
        echo "OPERATION SUMMARY:\n";
        printf("✅ Users created: %d\n", count($createdUsers));
        printf("🔄 Users updated: %d\n", count($updatedUsers));
        printf("📊 Total processed: %d\n", count($scorers));
        printf("🎯 All users assigned 'Scorer' role\n");

        // Save final credentials
        $output = "NO,NAME,USERNAME,EMAIL,PASSWORD,STATUS\n";
        foreach ($scorers as $index => $scorer) {
            $status = in_array($scorer, $createdUsers) ? 'CREATED' : 'UPDATED';
            $output .= sprintf('%d,"%s",%s,%s,%s,%s',
                $index + 1,
                $scorer['name'],
                $scorer['username'],
                $scorer['username'],
                $scorer['password'],
                $status
            ) . "\n";
        }

        file_put_contents('scorer_credentials_final.csv', $output);
        echo "\n📁 Final credentials saved to: scorer_credentials_final.csv\n";

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