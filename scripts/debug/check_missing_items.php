<?php

/**
 * CHECK MISSING ITEMS AND SNAPSHOT DATA
 */

echo "=== CHECKING MISSING ITEMS AND SNAPSHOT DATA ===\n\n";

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

    // Check delivery snapshots table
    echo "=== DELIVERY SNAPSHOTS TABLE ===\n";
    $stmt = $pdo->prepare("
        SELECT column_name, data_type, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'delivery_snapshots'
        ORDER BY ordinal_position
    ");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($columns) {
        foreach ($columns as $column) {
            echo "- {$column['column_name']}: {$column['data_type']}" .
                 ($column['is_nullable'] === 'YES' ? ' (nullable)' : '') . "\n";
        }

        // Check snapshot for delivery 22
        echo "\n=== SNAPSHOT FOR DELIVERY 22 ===\n";
        $stmt = $pdo->prepare("
            SELECT ds.snapshot, ds.total_items, ds.total_questions
            FROM delivery_snapshots ds
            WHERE ds.delivery_id = 22
        ");
        $stmt->execute();
        $snapshot = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($snapshot && $snapshot['snapshot']) {
            echo "✅ Snapshot found:\n";
            echo "  Total Items: {$snapshot['total_items']}\n";
            echo "  Total Questions: {$snapshot['total_questions']}\n";

            $snapshotData = json_decode($snapshot['snapshot'], true);
            if ($snapshotData && isset($snapshotData['exam_structure']['items'])) {
                $snapshotItems = count($snapshotData['exam_structure']['items']);
                $snapshotQuestions = 0;

                echo "  Parsed Snapshot Items: $snapshotItems\n";

                foreach ($snapshotData['exam_structure']['items'] as $index => $snapshotItem) {
                    $questionCount = count($snapshotItem['questions'] ?? []);
                    $snapshotQuestions += $questionCount;

                    $itemType = ($snapshotItem['is_vignette'] ?? false) ? 'VIGNETTE' : 'REGULAR';
                    echo sprintf("    Item #%2d: Hash %-8s | %s | %d questions\n",
                        ($index + 1),
                        $snapshotItem['hash'] ?? 'UNKNOWN',
                        $itemType,
                        $questionCount
                    );
                }

                echo "  Parsed Snapshot Questions: $snapshotQuestions\n\n";
            }
        } else {
            echo "❌ No snapshot found for delivery 22\n\n";
        }
    } else {
        echo "❌ delivery_snapshots table not found\n\n";
    }

    // Check what items should be there (look for missing MCQ numbers)
    echo "=== MISSING ITEMS ANALYSIS ===\n";
    echo "Expected: 60 items (MCQ 1-60)\n";
    echo "Found: 52 items in Exam 74\n\n";

    // Look for missing items by analyzing the pattern
    $stmt = $pdo->prepare("
        SELECT ei.order, i.id, i.title, i.hash, i.is_vignette
        FROM exam_item ei
        JOIN items i ON ei.item_id = i.id
        WHERE ei.exam_id = 74
        ORDER BY ei.order
    ");
    $stmt->execute();
    $examItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Current Exam 74 Items (by order):\n";
    echo "===================================\n";
    foreach ($examItems as $index => $item) {
        $order = $item['order'];
        $title = $item['title'];
        $type = $item['is_vignette'] ? 'VIGNETTE' : 'REGULAR';

        echo sprintf("Order %2d: %s | %s\n", $order, $type, substr($title, 0, 50));
    }

    // Check for gaps in order or missing MCQ numbers
    $orders = array_column($examItems, 'order');
    $missingOrders = [];
    for ($i = 1; $i <= 60; $i++) {
        if (!in_array($i, $orders)) {
            $missingOrders[] = $i;
        }
    }

    if (!empty($missingOrders)) {
        echo "\n⚠️  Missing orders: " . implode(', ', $missingOrders) . "\n";
        echo "   This suggests these positions should have items but don't\n";
    }

    // Check if there are other deliveries using the same exam
    echo "\n=== OTHER DELIVERIES USING EXAM 74 ===\n";
    $stmt = $pdo->prepare("
        SELECT d.id, d.name, d.duration, d.automatic_start
        FROM deliveries d
        WHERE d.exam_id = 74
        ORDER BY d.id
    ");
    $stmt->execute();
    $deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($deliveries as $del) {
        echo "Delivery {$del['id']}: {$del['name']} ({$del['duration']} min)\n";
    }

    // Check if there are any items that might be related but not in exam 74
    echo "\n=== CHECKING FOR RELATED ITEMS NOT IN EXAM 74 ===\n";
    $stmt = $pdo->prepare("
        SELECT i.id, i.title, i.hash, i.is_vignette
        FROM items i
        WHERE i.title LIKE 'BE 051125 - MCQ%'
        AND i.id NOT IN (
            SELECT ei.item_id
            FROM exam_item ei
            WHERE ei.exam_id = 74
        )
        ORDER BY i.title
    ");
    $stmt->execute();
    $orphanItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($orphanItems)) {
        echo "Found " . count($orphanItems) . " related items not in Exam 74:\n";
        foreach ($orphanItems as $item) {
            $type = $item['is_vignette'] ? 'VIGNETTE' : 'REGULAR';
            echo "  ID {$item['id']}: {$item['title']} | Hash {$item['hash']} | $type\n";
        }
    } else {
        echo "✅ No orphaned items found\n";
    }

    // Final Analysis
    echo "\n=== FINAL ANALYSIS ===\n";
    echo "Delivery 22 uses Exam 74 which contains:\n";
    echo "  - 52 items (should be 60)\n";
    echo "  - 55 questions (should be 60)\n";
    echo "  - 3 vignette items (each with 2 questions)\n";
    echo "  - 49 regular items (each with 1 question)\n\n";

    echo "Missing: 8 items and 5 questions\n\n";

    echo "This explains why:\n";
    echo "1. Backend only retrieves 52 items instead of 60\n";
    echo "2. Vignette questions work (3 vignettes × 2 = 6 questions)\n";
    echo "3. But total is less than expected 60 items\n\n";

    echo "The issue might be:\n";
    echo "- Exam 74 was created with only 52 items instead of 60\n";
    echo "- Or 8 items were removed from Exam 74\n";
    echo "- Or the exam setup is incomplete\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== CHECK COMPLETE ===\n";