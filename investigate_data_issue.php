<?php

/**
 * DEEP INVESTIGATION - Database vs Frontend Hash Mismatch
 */

echo "=== DEEP INVESTIGATION: DATA SYNCHRONIZATION ISSUE ===\n\n";

// Connect to database directly using production credentials
$host = '107.155.75.50';
$port = '5986';
$dbname = 'ionbec-new';
$username = 'postgres';
$password = '6LP0Ojegy7IUU6kaX9lLkmZRUiAdAUNOltWyL3LegfYGR6rPQtB4DUSVqjdA78ES';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ Database connected successfully\n\n";

    // Step 1: Check if items table exists and get sample data
    echo "=== STEP 1: CHECKING ITEMS TABLE ===\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total_items FROM items");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total items in database: " . $result['total_items'] . "\n\n";

    // Step 2: Get recent items from the specific exam (ID 73 from logs)
    echo "=== STEP 2: ITEMS FROM EXAM 73 ===\n";
    $stmt = $pdo->query("
        SELECT i.id, i.title, i.hash, ei.order
        FROM items i
        JOIN exam_item ei ON i.id = ei.item_id
        WHERE ei.exam_id = 73
        ORDER BY ei.order
        LIMIT 10
    ");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($items) . " items for exam 73:\n";
    foreach ($items as $item) {
        echo sprintf("- ID: %d, Hash: %s, Title: %s\n",
            $item['id'],
            $item['hash'] ?: 'NULL',
            substr($item['title'] ?: 'No title', 0, 50)
        );
    }
    echo "\n";

    // Step 3: Check specific hashes from logs
    echo "=== STEP 3: CHECKING SPECIFIC HASHES FROM LOGS ===\n";
    $problemHashes = [
        'pwBeWwgQ',  // BE 051125 - MCQ 1 & 2
        'r8gEGnBj',  // BE 051125 - MCQ 54
        'Adkn2GkR',  // BE 051125 - MCQ 24
    ];

    foreach ($problemHashes as $hash) {
        echo "Checking hash: $hash\n";

        $stmt = $pdo->prepare("SELECT id, title, hash FROM items WHERE hash = ?");
        $stmt->execute([$hash]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            echo "  ✅ FOUND: ID {$item['id']}, Title: " . substr($item['title'], 0, 50) . "\n";
        } else {
            echo "  ❌ NOT FOUND\n";

            // Try to find similar hashes
            $stmt = $pdo->prepare("SELECT id, title, hash FROM items WHERE hash LIKE ? LIMIT 3");
            $stmt->execute([substr($hash, 0, 4) . '%']);
            $similar = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($similar) {
                echo "  🔍 Similar hashes found:\n";
                foreach ($similar as $s) {
                    echo "    - {$s['hash']}: " . substr($s['title'], 0, 30) . "\n";
                }
            } else {
                echo "  🔍 No similar hashes found\n";
            }
        }
        echo "\n";
    }

    // Step 4: Check if there are ANY items with hashes
    echo "=== STEP 4: CHECKING HASH DISTRIBUTION ===\n";
    $stmt = $pdo->query("
        SELECT
            COUNT(CASE WHEN hash IS NOT NULL THEN 1 END) as with_hash,
            COUNT(CASE WHEN hash IS NULL THEN 1 END) as without_hash,
            COUNT(*) as total
        FROM items
    ");
    $hashStats = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "Hash distribution:\n";
    echo "- With hash: {$hashStats['with_hash']}\n";
    echo "- Without hash: {$hashStats['without_hash']}\n";
    echo "- Total: {$hashStats['total']}\n\n";

    // Step 5: Check if the exam items have questions
    echo "=== STEP 5: CHECKING QUESTIONS FOR EXAM 73 ===\n";
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT q.id) as total_questions
        FROM questions q
        JOIN items i ON q.item_id = i.id
        JOIN exam_item ei ON i.id = ei.item_id
        WHERE ei.exam_id = 73
    ");
    $questionCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total questions for exam 73: " . $questionCount['total_questions'] . "\n\n";

    // Step 6: Check delivery and snapshot info
    echo "=== STEP 6: CHECKING DELIVERY 20 ===\n";
    $stmt = $pdo->query("
        SELECT id, exam_id, snapshot, created_at, updated_at
        FROM deliveries
        WHERE id = 20
    ");
    $delivery = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($delivery) {
        echo "Delivery 20 found:\n";
        echo "- Exam ID: {$delivery['exam_id']}\n";
        echo "- Has snapshot: " . ($delivery['snapshot'] ? 'YES' : 'NO') . "\n";
        echo "- Created: {$delivery['created_at']}\n";
        echo "- Updated: {$delivery['updated_at']}\n";

        if ($delivery['snapshot']) {
            $snapshot = json_decode($delivery['snapshot'], true);
            if ($snapshot && isset($snapshot['exam_structure']['items'])) {
                echo "- Snapshot items count: " . count($snapshot['exam_structure']['items']) . "\n";
            }
        }
    } else {
        echo "❌ Delivery 20 not found\n";
    }

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

echo "\n=== INVESTIGATION COMPLETE ===\n";