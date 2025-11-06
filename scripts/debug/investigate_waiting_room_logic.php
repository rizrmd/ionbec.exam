<?php

echo "=== INVESTIGATING WAITING ROOM LOGIC FOR DELIVERY 9OGqXvLw ===\n";

// Direct PDO connection
try {
    $pdo = new PDO(
        "pgsql:host=107.155.75.50;port=5986;dbname=ionbec-new",
        "postgres",
        "6LP0Ojegy7IUU6kaX9lLkmZRUiAdAUNOltWyL3LegfYGR6rPQtB4DUSVqjdA78ES"
    );
    echo "✓ Database connected\n\n";
} catch (PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// 1. Get detailed delivery configuration
echo "1. DELIVERY 9OGqXvLw CONFIGURATION\n";
echo str_repeat("-", 60) . "\n";

$stmt = $pdo->prepare("
    SELECT * FROM deliveries WHERE hash = '9OGqXvLw'
");
$stmt->execute();
$delivery = $stmt->fetch(PDO::FETCH_ASSOC);

if ($delivery) {
    echo "✓ Delivery Found:\n";
    foreach ($delivery as $key => $value) {
        echo "  - $key: " . ($value ?? 'NULL') . "\n";
    }
} else {
    echo "❌ Delivery not found\n";
    exit(1);
}

echo "\n";

// 2. Check current time vs scheduled time
echo "2. TIME ANALYSIS\n";
echo str_repeat("-", 60) . "\n";

$currentDateTime = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
echo "Current Time (Jakarta): " . $currentDateTime->format('Y-m-d H:i:s') . "\n";

if ($delivery['scheduled_at']) {
    $scheduledTime = new DateTime($delivery['scheduled_at']);
    echo "Scheduled Time: " . $scheduledTime->format('Y-m-d H:i:s') . "\n";
    echo "Time until scheduled: " . $currentDateTime->diff($scheduledTime)->format('%R%H:%I:%S') . "\n";
    echo "Should be in waiting room: " . ($currentDateTime < $scheduledTime ? 'YES' : 'NO') . "\n";
    echo "Hours until exam: " . floor($currentDateTime->diff($scheduledTime)->h + $currentDateTime->diff($scheduledTime)->days * 24) . "\n";
}

if ($delivery['ended_at']) {
    $endTime = new DateTime($delivery['ended_at']);
    echo "End Time: " . $endTime->format('Y-m-d H:i:s') . "\n";
    echo "Exam has ended: " . ($currentDateTime > $endTime ? 'YES' : 'NO') . "\n";
}

echo "\n";

// 3. Check attempts and their status
echo "3. ALL ATTEMPTS FOR THIS DELIVERY\n";
echo str_repeat("-", 60) . "\n";

$stmt = $pdo->prepare("
    SELECT a.*, t.name as taker_name, t.email as taker_email
    FROM attempts a
    JOIN takers t ON a.attempted_by = t.id
    WHERE a.delivery_id = ?
    ORDER BY a.created_at DESC
");
$stmt->execute([$delivery['id']]);
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($attempts) . " attempts:\n";
foreach ($attempts as $attempt) {
    echo "  - Attempt ID: {$attempt['id']}, Hash: {$attempt['hash']}\n";
    echo "    → Taker: {$attempt['taker_name']}\n";
    echo "    → Created: {$attempt['created_at']}\n";
    echo "    → Started: " . ($attempt['started_at'] ?? 'NOT STARTED') . "\n";
    echo "    → Completed: " . ($attempt['completed_at'] ?? 'NOT COMPLETED') . "\n";
    echo "    → Status: " . ($attempt['status'] ?? 'NO STATUS') . "\n\n";
}

// 4. Check if there's any logic that bypasses waiting room
echo "4. CONFIGURATION ANALYSIS\n";
echo str_repeat("-", 60) . "\n";

echo "Key settings that affect waiting room:\n";
echo "  - is_anytime: " . ($delivery['is_anytime'] ? 'TRUE' : 'FALSE') . "\n";
echo "  - automatic_start: " . ($delivery['automatic_start'] ? 'TRUE' : 'FALSE') . "\n";
echo "  - last_status: " . ($delivery['last_status'] ?? 'NULL') . "\n";
echo "  - is_finished: " . ($delivery['is_finished'] ?? 'NULL') . "\n";

if ($delivery['is_anytime']) {
    echo "⚠️  WARNING: is_anytime = TRUE allows exam to start anytime!\n";
}

if ($delivery['automatic_start']) {
    echo "⚠️  WARNING: automatic_start = TRUE may bypass waiting room!\n";
}

// 5. Check the specific tokens mentioned
echo "\n5. SPECIFIC TOKENS ANALYSIS\n";
echo str_repeat("-", 60) . "\n";

$tokensToCheck = ['VSbXa', '62Iqg', 'YKTCA', 'Ya8uF', 'XuE3S', 'WANTb'];

foreach ($tokensToCheck as $token) {
    $stmt = $pdo->prepare("
        SELECT a.*, t.name as taker_name
        FROM attempts a
        JOIN takers t ON a.attempted_by = t.id
        WHERE a.hash = ? AND a.delivery_id = ?
    ");
    $stmt->execute([$token, $delivery['id']]);
    $attempt = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "Token: $token\n";
    if ($attempt) {
        echo "  → Found attempt for: {$attempt['taker_name']}\n";
        echo "  → Started: " . ($attempt['started_at'] ?? 'NOT STARTED') . "\n";
        echo "  → Status: " . ($attempt['status'] ?? 'NO STATUS') . "\n";
        echo "  → Should be in waiting room: " . ($attempt['started_at'] ? 'NO (already started)' : 'YES') . "\n";
    } else {
        echo "  → No attempt found for this token\n";
        echo "  → Should be in waiting room: YES\n";
    }
    echo "\n";
}

echo "\n=== INVESTIGATION COMPLETE ===\n";