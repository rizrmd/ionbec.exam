<?php

// Simulate the exact logic from PublicTokenLoginController

echo "=== Debug PublicTokenLoginController Logic ===\n";

// Set timezone like Laravel does
date_default_timezone_set('Asia/Jakarta');

// Simulate the token
$token = 'ykTcA';
echo "Testing token: $token\n";
echo "Current PHP time: " . date('Y-m-d H:i:s') . "\n";

// Database connection
$host = '107.155.75.50';
$port = '5986';
$dbname = 'ionbec-new';
$user = 'postgres';
$password = '6LP0Ojegy7IUU6kaX9lLkmZRUiAdAUNOltWyL3LegfYGR6rPQtB4DUSVqjdA78ES';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET TIME ZONE 'Asia/Jakarta'");

    echo "✅ Database connected with Jakarta timezone\n";

    // Step 1: Find delivery_taker record (exact logic from controller)
    $stmt = $pdo->prepare("SELECT * FROM delivery_taker WHERE token = ?");
    $stmt->execute([strtoupper($token)]);
    $deliveryTaker = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$deliveryTaker) {
        echo "❌ Token not found in delivery_taker\n";
        exit;
    }

    echo "✅ Found delivery_taker record\n";
    echo "   delivery_id: {$deliveryTaker['delivery_id']}\n";
    echo "   taker_id: {$deliveryTaker['taker_id']}\n";
    echo "   is_login: " . ($deliveryTaker['is_login'] ? 'true' : 'false') . "\n";

    // Step 2: Get delivery details
    $stmt = $pdo->prepare("SELECT * FROM deliveries WHERE id = ?");
    $stmt->execute([$deliveryTaker['delivery_id']]);
    $delivery = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$delivery) {
        echo "❌ Delivery not found\n";
        exit;
    }

    echo "✅ Found delivery\n";
    echo "   name: {$delivery['name']}\n";
    echo "   scheduled_at: {$delivery['scheduled_at']}\n";
    echo "   automatic_start: " . ($delivery['automatic_start'] ? 'true' : 'false') . "\n";
    echo "   ended_at: " . ($delivery['ended_at'] ?? 'null') . "\n";

    // Step 3: Check if delivery has expired (exact controller logic)
    if ($delivery['ended_at'] && strtotime('now') > strtotime($delivery['ended_at'])) {
        echo "❌ Delivery has expired!\n";
        echo "   ended_at: {$delivery['ended_at']}\n";
        echo "   current_time: " . date('Y-m-d H:i:s') . "\n";
        exit;
    }

    echo "✅ Delivery is still active\n";

    // Step 4: THE CRITICAL LOGIC - Check if should redirect to waiting room
    echo "\n=== WAITING ROOM DECISION ===\n";

    $automaticStart = $delivery['automatic_start'];
    $scheduledTime = strtotime($delivery['scheduled_at']);
    $currentTime = strtotime('now');
    $condition = $automaticStart && $scheduledTime > $currentTime;

    echo "automatic_start: " . ($automaticStart ? 'true' : 'false') . "\n";
    echo "scheduled_at: {$delivery['scheduled_at']} (timestamp: $scheduledTime)\n";
    echo "current_time: " . date('Y-m-d H:i:s') . " (timestamp: $currentTime)\n";
    echo "scheduled_at > current_time: " . ($scheduledTime > $currentTime ? 'true' : 'false') . "\n";
    echo "FINAL CONDITION: " . ($condition ? 'TRUE' : 'FALSE') . "\n";

    if ($condition) {
        echo "🎯 RESULT: REDIRECT TO WAITING ROOM (/exam/waiting-room)\n";
    } else {
        echo "🎯 RESULT: REDIRECT DIRECTLY TO EXAM (/exam)\n";
    }

    // Step 5: Show what the database time actually is
    $dbTime = $pdo->query("SELECT NOW() as db_time")->fetch(PDO::FETCH_ASSOC);
    echo "\n=== DATABASE TIME VERIFICATION ===\n";
    echo "Database NOW(): " . $dbTime['db_time'] . "\n";
    echo "PHP NOW(): " . date('Y-m-d H:i:s') . "\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";