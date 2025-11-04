<?php

/**
 * CRITICAL INVESTIGATION: Delivery Items Relationship Analysis
 *
 * Why do some hashes fail in getQuestions() even though they exist in database?
 */

echo "=== DELIVERY ITEMS RELATIONSHIP INVESTIGATION ===\n\n";

// Connect to database
$host = '107.155.75.50';
$port = '5986';
$dbname = 'ionbec-new';
$username = 'postgres';
$password = '6LP0Ojegy7IUU6kaX9lLkmZRUiAdAUNOltWyL3LegfYGR6rPQtB4DUSVqjdA78ES';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ Database connected\n\n";

    // Current delivery ID from logs: 21
    $deliveryId = 21;
    $examId = 74;

    echo "=== DELIVERY ANALYSIS ===\n";
    echo "Delivery ID: $deliveryId\n";
    echo "Exam ID: $examId\n\n";

    // Check delivery items
    $stmt = $pdo->prepare("
        SELECT di.item_id, i.id, i.hash, i.title, i.created_at, i.updated_at
        FROM delivery_items di
        JOIN items i ON di.item_id = i.id
        WHERE di.delivery_id = ?
        ORDER BY di.order_index
    ");
    $stmt->execute([$deliveryId]);
    $deliveryItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Delivery items count: " . count($deliveryItems) . "\n\n";

    // Check failing hashes vs delivery items
    $failingHashes = [
        '0Qk8Pqko', // MCQ 30 - Item not found in getQuestions
        'lAKjyXg9', // MCQ 42 - Item not found
        'qZgleXK5', // MCQ 5  - Item not found
        'DxKJDpkq', // MCQ 13 - Item not found
        'MlK1PYgN', // MCQ 14 - Item not found
    ];

    $workingHashes = [
        '3ZBYv4k0', // MCQ 11 - Item found
        '53gDGMky', // MCQ 9 & 10 - Item found
        'xDkVA1BX', // MCQ 40 - Item found
    ];

    echo "=== FAILING HASHES - DELIVERY CHECK ===\n";
    foreach ($failingHashes as $hash) {
        $stmt = $pdo->prepare("
            SELECT i.id, i.title, i.hash, di.order_index
            FROM items i
            LEFT JOIN delivery_items di ON i.id = di.item_id AND di.delivery_id = ?
            WHERE i.hash = ?
        ");
        $stmt->execute([$deliveryId, $hash]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            if ($result['order_index'] !== null) {
                echo "✅ IN DELIVERY: $hash - Order: {$result['order_index']} - {$result['title']}\n";
            } else {
                echo "❌ NOT IN DELIVERY: $hash - {$result['title']} (Missing from delivery_items)\n";
            }
        } else {
            echo "❌ NOT FOUND: $hash - Item doesn't exist\n";
        }
    }

    echo "\n=== WORKING HASHES - DELIVERY CHECK ===\n";
    foreach ($workingHashes as $hash) {
        $stmt = $pdo->prepare("
            SELECT i.id, i.title, i.hash, di.order_index
            FROM items i
            LEFT JOIN delivery_items di ON i.id = di.item_id AND di.delivery_id = ?
            WHERE i.hash = ?
        ");
        $stmt->execute([$deliveryId, $hash]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            if ($result['order_index'] !== null) {
                echo "✅ IN DELIVERY: $hash - Order: {$result['order_index']} - {$result['title']}\n";
            } else {
                echo "❌ NOT IN DELIVERY: $hash - {$result['title']} (Missing from delivery_items)\n";
            }
        } else {
            echo "❌ NOT FOUND: $hash - Item doesn't exist\n";
        }
    }

    echo "\n=== EXAM ITEMS vs DELIVERY ITEMS ===\n";

    // Check exam items
    $stmt = $pdo->prepare("
        COUNT(DISTINCT ei.item_id) as exam_items_count
        FROM exam_items ei
        WHERE ei.exam_id = ?
    ");
    $stmt->execute([$examId]);
    $examItemsCount = $stmt->fetchColumn();

    echo "Exam items count: $examItemsCount\n";
    echo "Delivery items count: " . count($deliveryItems) . "\n";

    // Check for snapshot
    $stmt = $pdo->prepare("SELECT id, total_questions, created_at FROM delivery_snapshots WHERE delivery_id = ?");
    $stmt->execute([$deliveryId]);
    $snapshot = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($snapshot) {
        echo "Delivery snapshot exists: {$snapshot['total_questions']} questions (Created: {$snapshot['created_at']})\n";
    } else {
        echo "No delivery snapshot found\n";
    }

    echo "\n=== POTENTIAL ISSUE ===\n";
    echo "If items exist in database but fail in getQuestions(), possible causes:\n";
    echo "1. Items not in delivery_items table for this delivery\n";
    echo "2. MainController using different data source (snapshot vs database)\n";
    echo "3. Query scope issues (client scope, etc.)\n";
    echo "4. Timing issues with database transactions\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== INVESTIGATION COMPLETE ===\n";