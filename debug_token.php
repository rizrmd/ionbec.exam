<?php

require_once 'vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    // Connect to PostgreSQL database
    $host = $_ENV['DB_HOST'] ?? '107.155.75.50';
    $port = $_ENV['DB_PORT'] ?? '5986';
    $database = $_ENV['DB_DATABASE'] ?? 'ionbec-new';
    $username = $_ENV['DB_USERNAME'] ?? 'postgres';
    $password = $_ENV['DB_PASSWORD'] ?? '6LP0Ojegy7IUU6kaX9lLkmZRUiAdAUNOltWyL3LegfYGR6rPQtB4DUSVqjdA78ES';

    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== Checking token 'OpRy9' in delivery_taker table ===\n\n";

    // Check if token exists
    $stmt = $pdo->prepare("SELECT dt.*, d.name as delivery_name, d.ended_at, d.duration FROM delivery_taker dt JOIN deliveries d ON dt.delivery_id = d.id WHERE dt.token = :token");
    $stmt->execute(['token' => 'OpRy9']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($results)) {
        echo "❌ Token 'OpRy9' NOT FOUND in delivery_taker table\n\n";

        // Let's check if there are any similar tokens
        $stmt = $pdo->prepare("SELECT token FROM delivery_taker WHERE token ILIKE '%opry9%' OR token ILIKE '%OPR%' LIMIT 10");
        $stmt->execute();
        $similar = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($similar)) {
            echo "Similar tokens found:\n";
            foreach ($similar as $row) {
                echo "- " . $row['token'] . "\n";
            }
        }
    } else {
        echo "✅ Token 'OpRy9' FOUND!\n\n";
        foreach ($results as $row) {
            echo "Delivery Taker ID: " . $row['id'] . "\n";
            echo "Token: " . $row['token'] . "\n";
            echo "Delivery ID: " . $row['delivery_id'] . "\n";
            echo "Delivery Name: " . $row['delivery_name'] . "\n";
            echo "Group ID: " . $row['group_id'] . "\n";
            echo "Created At: " . $row['created_at'] . "\n";
            echo "Updated At: " . $row['updated_at'] . "\n";
            echo "Delivery Ended At: " . $row['ended_at'] . "\n";
            echo "Delivery Duration: " . $row['duration'] . "\n";

            // Check if delivery is still active
            $endedAt = new DateTime($row['ended_at']);
            $now = new DateTime();
            $isActive = $now < $endedAt;

            echo "Delivery Status: " . ($isActive ? "✅ ACTIVE" : "❌ EXPIRED") . "\n";
            echo "\n";
        }
    }

    // Check if there are any attempts for this token
    if (!empty($results)) {
        echo "=== Checking attempts for this delivery ===\n\n";
        $deliveryId = $results[0]['delivery_id'];

        // First check table structure
        echo "Checking attempts table structure...\n";
        $stmt = $pdo->prepare("SELECT column_name FROM information_schema.columns WHERE table_name = 'attempts' ORDER BY ordinal_position");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Columns in attempts table: " . implode(", ", array_column($columns, 'column_name')) . "\n\n";

        // Check attempts for this delivery
        $stmt = $pdo->prepare("SELECT a.*, taker.id as taker_id, taker.token FROM attempts a JOIN delivery_taker taker ON a.delivery_id = taker.delivery_id WHERE a.delivery_id = :delivery_id AND taker.token = :token ORDER BY a.created_at DESC");
        $stmt->execute(['delivery_id' => $deliveryId, 'token' => 'OpRy9']);
        $attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($attempts)) {
            echo "❌ No attempts found for this delivery and token\n";

            // Check if there are any attempts for this delivery at all
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM attempts WHERE delivery_id = :delivery_id");
            $stmt->execute(['delivery_id' => $deliveryId]);
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "Total attempts for this delivery: " . $count['count'] . "\n";
        } else {
            echo "✅ Found " . count($attempts) . " attempt(s):\n";
            foreach ($attempts as $attempt) {
                echo "- Attempt ID: " . $attempt['id'] . " (Hash: " . $attempt['hash'] . ")\n";
                echo "  Toker ID: " . $attempt['taker_id'] . "\n";
                echo "  Token: " . $attempt['token'] . "\n";
                echo "  Created: " . $attempt['created_at'] . "\n";
                echo "  Updated: " . $attempt['updated_at'] . "\n";
                echo "  Ended At: " . $attempt['ended_at'] . "\n";
                echo "  Is Submitted: " . ($attempt['is_submitted'] ? "Yes" : "No") . "\n";

                // Check attempt questions for this attempt
                $stmt2 = $pdo->prepare("SELECT COUNT(*) as count FROM attempt_questions WHERE attempt_id = :attempt_id");
                $stmt2->execute(['attempt_id' => $attempt['id']]);
                $questionCount = $stmt2->fetch(PDO::FETCH_ASSOC);
                echo "  Number of answered questions: " . $questionCount['count'] . "\n";
                echo "\n";
            }
        }
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}