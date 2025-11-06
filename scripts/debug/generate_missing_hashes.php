<?php

echo "=== GENERATING MISSING HASHES ===\n";

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

// Function to generate hash
function generateHash() {
    return substr(strtoupper(md5(uniqid(rand(), true))), 0, 8);
}

// 1. Check items without hash
echo "1. ITEMS WITHOUT HASH\n";
echo str_repeat("-", 40) . "\n";

$stmt = $pdo->prepare("SELECT id, title FROM items WHERE hash IS NULL OR hash = ''");
$stmt->execute();
$itemsWithoutHash = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($itemsWithoutHash) . " items without hash\n\n";

if (count($itemsWithoutHash) > 0) {
    echo "Generating hashes...\n";

    foreach ($itemsWithoutHash as $item) {
        $hash = generateHash();

        // Check if hash already exists
        $checkStmt = $pdo->prepare("SELECT COUNT(*) as count FROM items WHERE hash = ?");
        $checkStmt->execute([$hash]);
        $hashExists = $checkStmt->fetch(PDO::FETCH_ASSOC);

        // Generate unique hash
        while ($hashExists['count'] > 0) {
            $hash = generateHash();
            $checkStmt->execute([$hash]);
            $hashExists = $checkStmt->fetch(PDO::FETCH_ASSOC);
        }

        // Update item with hash
        $updateStmt = $pdo->prepare("UPDATE items SET hash = ? WHERE id = ?");
        $success = $updateStmt->execute([$hash, $item['id']]);

        if ($success) {
            echo "  ✓ Item ID {$item['id']} ({$item['title']}) → Hash: {$hash}\n";
        } else {
            echo "  ✗ Failed to update Item ID {$item['id']}\n";
        }
    }
}

// 2. Verify all items now have hash
echo "\n2. VERIFICATION\n";
echo str_repeat("-", 40) . "\n";

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM items WHERE hash IS NOT NULL AND hash != ''");
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Items with hash: {$result['total']}\n";

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM items");
$stmt->execute();
$totalItems = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Total items: {$totalItems['total']}\n";

if ($result['total'] == $totalItems['total']) {
    echo "✓ All items now have hash!\n";
} else {
    echo "⚠️  Some items still missing hash\n";
}

// 3. Update attempts that might need taker code
echo "\n3. UPDATE ATTEMPT TAKER CODES\n";
echo str_repeat("-", 40) . "\n";

// Find attempts with empty taker_code
$stmt = $pdo->prepare("SELECT id, attempted_by, delivery_id FROM attempts WHERE taker_code IS NULL OR taker_code = ''");
$stmt->execute();
$attemptsWithoutCode = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($attemptsWithoutCode) . " attempts without taker code\n";

foreach ($attemptsWithoutCode as $attempt) {
    // Get delivery info
    $deliveryStmt = $pdo->prepare("SELECT d.group_id, g.code FROM deliveries d JOIN groups g ON d.group_id = g.id WHERE d.id = ?");
    $deliveryStmt->execute([$attempt['delivery_id']]);
    $deliveryInfo = $deliveryStmt->fetch(PDO::FETCH_ASSOC);

    if ($deliveryInfo) {
        $takerCode = $deliveryInfo['code'] . '-' . str_pad($attempt['attempted_by'], 3, '0', STR_PAD_LEFT);

        $updateStmt = $pdo->prepare("UPDATE attempts SET taker_code = ? WHERE id = ?");
        $success = $updateStmt->execute([$takerCode, $attempt['id']]);

        if ($success) {
            echo "  ✓ Attempt ID {$attempt['id']} → Taker Code: {$takerCode}\n";
        }
    }
}

echo "\n=== HASH GENERATION COMPLETE ===\n";