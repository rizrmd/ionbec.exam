<?php

/**
 * CHECK DELIVERIES TABLE STRUCTURE AND EXAM 74 DETAILS
 */

echo "=== CHECKING DELIVERIES TABLE & EXAM 74 DETAILS ===\n\n";

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

    // Check deliveries table structure
    echo "=== DELIVERIES TABLE STRUCTURE ===\n";
    $stmt = $pdo->prepare("
        SELECT column_name, data_type, is_nullable, column_default
        FROM information_schema.columns
        WHERE table_name = 'deliveries'
        ORDER BY ordinal_position
    ");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $column) {
        echo "- {$column['column_name']}: {$column['data_type']}" .
             ($column['is_nullable'] === 'YES' ? ' (nullable)' : '') .
             ($column['column_default'] ? " (default: {$column['column_default']})" : '') . "\n";
    }
    echo "\n";

    // Check Delivery 22 details
    echo "=== DELIVERY 22 DETAILS ===\n";
    $stmt = $pdo->prepare("
        SELECT d.id, d.name, d.exam_id, d.duration, d.automatic_start, d.scheduled_at,
               e.name as exam_name, e.title as exam_title
        FROM deliveries d
        JOIN exams e ON d.exam_id = e.id
        WHERE d.id = 22
    ");
    $stmt->execute();
    $delivery = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($delivery) {
        echo "✅ Delivery 22 Found:\n";
        echo "  ID: {$delivery['id']}\n";
        echo "  Name: {$delivery['name']}\n";
        echo "  Exam ID: {$delivery['exam_id']}\n";
        echo "  Exam Name: {$delivery['exam_name']}\n";
        echo "  Exam Title: {$delivery['exam_title']}\n";
        echo "  Duration: {$delivery['duration']} minutes\n";
        echo "  Auto Start: " . ($delivery['automatic_start'] ? 'YES' : 'NO') . "\n\n";
    } else {
        echo "❌ Delivery 22 not found\n";
        exit;
    }

    // Check Exam 74 complete structure
    echo "=== EXAM 74 COMPLETE STRUCTURE ===\n";
    $examId = $delivery['exam_id'];

    // Get all items in exam with question counts
    $stmt = $pdo->prepare("
        SELECT ei.exam_id, ei.item_id, ei.order,
               i.id, i.title, i.hash, i.is_vignette, i.type,
               COUNT(q.id) as question_count
        FROM exam_item ei
        JOIN items i ON ei.item_id = i.id
        LEFT JOIN questions q ON i.id = q.item_id
        WHERE ei.exam_id = ?
        GROUP BY ei.exam_id, ei.item_id, ei.order, i.id, i.title, i.hash, i.is_vignette, i.type
        ORDER BY ei.order
    ");
    $stmt->execute([$examId]);
    $examItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalItems = count($examItems);
    $totalQuestions = 0;
    $vignetteCount = 0;
    $regularCount = 0;

    echo "Exam $examId Summary:\n";
    echo "===================\n";

    foreach ($examItems as $index => $item) {
        $totalQuestions += $item['question_count'];
        if ($item['is_vignette']) {
            $vignetteCount++;
        } else {
            $regularCount++;
        }
    }

    echo "Total Items: $totalItems\n";
    echo "Total Questions: $totalQuestions\n";
    echo "Vignette Items: $vignetteCount\n";
    echo "Regular Items: $regularCount\n\n";

    // Show detailed breakdown
    echo "Detailed Item Breakdown:\n";
    echo "========================\n";

    foreach ($examItems as $index => $item) {
        $itemType = $item['is_vignette'] ? 'VIGNETTE' : 'REGULAR';
        echo sprintf("Item #%2d: ID %-4d | Hash %-8s | %s | %d questions\n",
            ($index + 1),
            $item['id'],
            $item['hash'],
            $itemType,
            $item['question_count']
        );
        echo "         Title: " . substr($item['title'], 0, 60) . "...\n\n";
    }

    // Check if this should be 60 items
    echo "=== 60 ITEMS VERIFICATION ===\n";
    if ($totalItems !== 60) {
        echo "⚠️  Exam has $totalItems items, expected 60 items\n";
        echo "   Difference: " . ($totalItems - 60) . " items\n";
    } else {
        echo "✅ Exam has exactly 60 items as expected\n";
    }

    if ($totalQuestions !== 60) {
        echo "⚠️  Exam has $totalQuestions questions, expected 60 questions\n";
        echo "   Difference: " . ($totalQuestions - 60) . " questions\n";
    } else {
        echo "✅ Exam has exactly 60 questions as expected\n";
    }

    // Check snapshot data
    echo "\n=== SNAPSHOT VERIFICATION ===\n";
    $stmt = $pdo->prepare("
        SELECT snapshot
        FROM deliveries
        WHERE id = 22
    ");
    $stmt->execute();
    $deliverySnapshot = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($deliverySnapshot && $deliverySnapshot['snapshot']) {
        echo "✅ Snapshot found in delivery\n";

        $snapshot = json_decode($deliverySnapshot['snapshot'], true);
        if ($snapshot && isset($snapshot['exam_structure']['items'])) {
            $snapshotItems = count($snapshot['exam_structure']['items']);
            $snapshotQuestions = 0;

            foreach ($snapshot['exam_structure']['items'] as $snapshotItem) {
                $snapshotQuestions += count($snapshotItem['questions'] ?? []);
            }

            echo "  Snapshot Items: $snapshotItems\n";
            echo "  Snapshot Questions: $snapshotQuestions\n\n";

            // Compare snapshot with actual exam
            if ($snapshotItems !== $totalItems) {
                echo "⚠️  Snapshot vs Actual Items: $snapshotItems vs $totalItems\n";
            } else {
                echo "✅ Snapshot items match actual items\n";
            }

            if ($snapshotQuestions !== $totalQuestions) {
                echo "⚠️  Snapshot vs Actual Questions: $snapshotQuestions vs $totalQuestions\n";
            } else {
                echo "✅ Snapshot questions match actual questions\n";
            }
        }
    } else {
        echo "❌ No snapshot found\n";
    }

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== CHECK COMPLETE ===\n";