<?php

// Set timezone
date_default_timezone_set('Asia/Jakarta');

echo "=== Direct Token Logic Test ===\n";

// Test with direct database query to avoid Carbon issues
$token = 'YKTCA'; // Uppercase version

echo "Testing token: $token\n";

// Database connection
$host = '107.155.75.50';
$port = '5986';
$dbname = 'ionbec-new';
$user = 'postgres';
$password = '6LP0Ojegy7IUU6kaX9lLkmZRUiAdAUNOltWyL3LegfYGR6rPQtB4DUSVqjdA78ES';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Set timezone for this session
    $pdo->exec("SET TIME ZONE 'Asia/Jakarta'");

    echo "✅ Database connected with Asia/Jakarta timezone\n";

    // Get delivery_taker
    $stmt = $pdo->prepare("SELECT dt.*, d.name as delivery_name, d.scheduled_at, d.automatic_start FROM delivery_taker dt JOIN deliveries d ON dt.delivery_id = d.id WHERE UPPER(dt.token) = ?");
    $stmt->execute([$token]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        echo "❌ Token not found\n";
        exit;
    }

    echo "✅ Token found\n";
    echo "   Delivery: {$result['delivery_name']}\n";
    echo "   Scheduled At: {$result['scheduled_at']}\n";
    echo "   Automatic Start: " . ($result['automatic_start'] ? 'true' : 'false') . "\n";

    // Test the exact logic from PublicTokenLoginController
    $scheduledTime = strtotime($result['scheduled_at']);
    $currentTime = strtotime('now');
    $timeDiff = $scheduledTime - $currentTime;

    echo "\n=== Time Logic Test ===\n";
    echo "Scheduled Time (strtotime): $scheduledTime\n";
    echo "Current Time (strtotime): $currentTime\n";
    echo "Time Difference: $timeDiff seconds (" . round($timeDiff/60, 2) . " minutes)\n";

    // Test the condition
    $shouldGoToWaitingRoom = $result['automatic_start'] && $scheduledTime > $currentTime;

    echo "\n=== Routing Decision ===\n";
    echo "Condition: automatic_start=true && scheduled_at > now\n";
    echo "automatic_start: " . ($result['automatic_start'] ? 'true' : 'false') . "\n";
    echo "scheduled_at > now: " . ($scheduledTime > $currentTime ? 'true' : 'false') . "\n";
    echo "Final Result: " . ($shouldGoToWaitingRoom ? 'WAITING ROOM' : 'DIRECT TO EXAM') . "\n";

    // Get current database time for comparison
    $dbTime = $pdo->query("SELECT NOW() as db_time")->fetch(PDO::FETCH_ASSOC);
    echo "\n=== Time Comparison ===\n";
    echo "PHP Current Time: " . date('Y-m-d H:i:s') . "\n";
    echo "Database Current Time: " . $dbTime['db_time'] . "\n";
    echo "Delivery Scheduled: " . $result['scheduled_at'] . "\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";