<?php

echo "=== CHECKING EXISTING ATTEMPTS FOR TOKEN 62Iqg ===\n";

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

// 1. Find dr. Rahadiyan Rheza Dewanto's info
echo "1. FINDING dr. Rahadiyan Rheza Dewanto INFO\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->prepare("
    SELECT * FROM takers WHERE name = 'dr. Rahadiyan Rheza Dewanto'
");
$stmt->execute();
$taker = $stmt->fetch(PDO::FETCH_ASSOC);

if ($taker) {
    echo "✓ Taker Found:\n";
    foreach ($taker as $key => $value) {
        echo "  - $key: " . ($value ?? 'NULL') . "\n";
    }
} else {
    echo "❌ Taker not found\n";
    exit(1);
}

echo "\n";

// 2. Check all attempts for this taker
echo "2. ALL ATTEMPTS FOR dr. Rahadiyan Rheza Dewanto\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->prepare("
    SELECT a.*, d.name as delivery_name, d.hash as delivery_hash
    FROM attempts a
    JOIN deliveries d ON a.delivery_id = d.id
    WHERE a.attempted_by = ?
    ORDER BY a.created_at DESC
");
$stmt->execute([$taker['id']]);
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($attempts) . " attempts:\n";
foreach ($attempts as $attempt) {
    echo "  - Attempt ID: {$attempt['id']}, Hash: {$attempt['hash']}\n";
    echo "    → Delivery: {$attempt['delivery_name']} ({$attempt['delivery_hash']})\n";
    echo "    → Created: {$attempt['created_at']}\n";
    echo "    → Started: " . ($attempt['started_at'] ?? 'NOT STARTED') . "\n";
    echo "    → Completed: " . ($attempt['completed_at'] ?? 'NOT COMPLETED') . "\n";
    echo "    → Status: " . ($attempt['status'] ?? 'NO STATUS') . "\n\n";
}

// 3. Check if there's an attempt with hash 62Iqg
echo "3. CHECKING FOR TOKEN 62Iqg\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->prepare("
    SELECT a.*, t.name as taker_name
    FROM attempts a
    JOIN takers t ON a.attempted_by = t.id
    WHERE a.hash = '62Iqg'
");
$stmt->execute();
$attemptWithToken = $stmt->fetch(PDO::FETCH_ASSOC);

if ($attemptWithToken) {
    echo "✓ Found attempt with token 62Iqg:\n";
    echo "  - Attempt ID: {$attemptWithToken['id']}\n";
    echo "  - Taker: {$attemptWithToken['taker_name']}\n";
    echo "  - Started: " . ($attemptWithToken['started_at'] ?? 'NOT STARTED') . "\n";
    echo "  - Status: " . ($attemptWithToken['status'] ?? 'NO STATUS') . "\n";
    echo "  - This is why token goes directly to exam!\n";
} else {
    echo "❌ No attempt found with token 62Iqg\n";
    echo "  → System might be reusing existing attempt with different logic\n";
}

// 4. Check what token mapping exists for this taker
echo "\n4. TOKEN MAPPING IN DELIVERY SYSTEM\n";
echo str_repeat("-", 50) . "\n";

// Check from the delivery taker view data we saw earlier
echo "From delivery taker page, we saw:\n";
echo "  - dr. Rahadiyan Rheza Dewanto (BE 051125-006)\n";
echo "  - Token: 62Iqg\n";
echo "  - Status: Attempt Now\n\n";

echo "This suggests there's a mapping system that:\n";
echo "1. Assigns token 62Iqg to this taker\n";
echo "2. Creates attempt when first used\n";
echo "3. Reuses same attempt on subsequent uses\n";

echo "\n=== ANALYSIS COMPLETE ===\n";