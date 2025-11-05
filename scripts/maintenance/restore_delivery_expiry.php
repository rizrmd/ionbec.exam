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

    echo "=== Restoring Delivery TEST25 Expiry Date ===\n\n";

    // Restore to original expired date
    $originalEndDate = '2025-11-04 02:00:00';

    // Update the delivery expiry date back to original
    $stmt = $pdo->prepare("UPDATE deliveries SET ended_at = :original_end_date WHERE id = 151");
    $stmt->execute(['original_end_date' => $originalEndDate]);

    echo "✅ Delivery expiry date RESTORED to original!\n\n";

    // Verify the restoration
    $stmt = $pdo->prepare("SELECT id, name, ended_at, duration FROM deliveries WHERE id = 151");
    $stmt->execute();
    $delivery = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "Restored Delivery Status:\n";
    echo "- ID: " . $delivery['id'] . "\n";
    echo "- Name: " . $delivery['name'] . "\n";
    echo "- Ended At: " . $delivery['ended_at'] . "\n";
    echo "- Duration: " . $delivery['duration'] . " minutes\n\n";

    // Check if delivery is now expired
    $now = new DateTime();
    $endedAt = new DateTime($delivery['ended_at']);
    $isExpired = $now > $endedAt;

    echo "Delivery Status: " . ($isExpired ? "❌ EXPIRED" : "✅ ACTIVE") . "\n";

    if ($isExpired) {
        echo "\n🔒 Token 'OpRy9' should now be REJECTED due to expired delivery\n";
        echo "   We need to implement proper error handling to show expired message\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}