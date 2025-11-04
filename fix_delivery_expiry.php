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

    echo "=== Fixing Delivery TEST25 Expiry Date ===\n\n";

    // Check current delivery status
    $stmt = $pdo->prepare("SELECT id, name, ended_at, duration FROM deliveries WHERE id = 151");
    $stmt->execute();
    $delivery = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($delivery) {
        echo "Current Delivery Status:\n";
        echo "- ID: " . $delivery['id'] . "\n";
        echo "- Name: " . $delivery['name'] . "\n";
        echo "- Current Ended At: " . $delivery['ended_at'] . "\n";
        echo "- Duration: " . $delivery['duration'] . " minutes\n\n";

        // Calculate new expiry date (extend by 7 days from now)
        $now = new DateTime();
        $newEndDate = new DateTime();
        $newEndDate->add(new DateInterval('P7D')); // Add 7 days

        echo "New expiry date will be: " . $newEndDate->format('Y-m-d H:i:s') . "\n";
        echo "Current date/time: " . $now->format('Y-m-d H:i:s') . "\n\n";

        // Update the delivery expiry date
        $stmt = $pdo->prepare("UPDATE deliveries SET ended_at = :new_end_date WHERE id = 151");
        $stmt->execute(['new_end_date' => $newEndDate->format('Y-m-d H:i:s')]);

        echo "✅ Delivery expiry date UPDATED successfully!\n\n";

        // Verify the update
        $stmt = $pdo->prepare("SELECT id, name, ended_at, duration FROM deliveries WHERE id = 151");
        $stmt->execute();
        $updatedDelivery = $stmt->fetch(PDO::FETCH_ASSOC);

        echo "Updated Delivery Status:\n";
        echo "- ID: " . $updatedDelivery['id'] . "\n";
        echo "- Name: " . $updatedDelivery['name'] . "\n";
        echo "- New Ended At: " . $updatedDelivery['ended_at'] . "\n";
        echo "- Duration: " . $updatedDelivery['duration'] . " minutes\n\n";

        // Check if delivery is now active
        $endedAt = new DateTime($updatedDelivery['ended_at']);
        $isActive = $now < $endedAt;

        echo "Delivery Status: " . ($isActive ? "✅ ACTIVE" : "❌ EXPIRED") . "\n";

        if ($isActive) {
            echo "\n🎉 Token 'OpRy9' should now work for saving answers!\n";
        }

    } else {
        echo "❌ Delivery with ID 151 not found\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}