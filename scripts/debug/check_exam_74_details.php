<?php

/**
 * CHECK EXAM 74 COMPLETE DETAILS
 */

echo "=== CHECKING EXAM 74 COMPLETE DETAILS ===\n\n";

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

    // Step 1: Check Delivery 22 details
    echo "=== STEP 1: DELIVERY 22 DETAILS ===\n";
    $stmt = $pdo->prepare("
        SELECT d.id, d.name, d.exam_id, d.total_items, d.total_questions,
               d.duration, d.automatic_start, d.scheduled_at,
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
        echo "  Total Items (delivery): {$delivery['total_items']}\n";
        echo "  Total Questions (delivery): {$delivery['total_questions']}\n";
        echo "  Duration: {$delivery['duration']} minutes\n";
        echo "  Auto Start: " . ($delivery['automatic_start'] ? 'YES' : 'NO') . "\n";
        echo "  Scheduled: {$delivery['scheduled_at']}\n\n";
    } else {
        echo "❌ Delivery 22 not found\n";
        exit;
    }

    // Step 2: Check Exam 74 complete structure
    echo "=== STEP 2: EXAM 74 COMPLETE STRUCTURE ===\n";
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

    echo "Exam $examId Complete Item List:\n";
    echo "================================\n";

    $totalItems = count($examItems);
    $totalQuestions = 0;
    $vignetteCount = 0;
    $regularCount = 0;

    foreach ($examItems as $index => $item) {
        echo "Item #" . str_pad(($index + 1), 2, ' ', STR_PAD_LEFT) . "\n";
        echo "  ID: {$item['id']}\n";
        echo "  Hash: {$item['hash']}\n";
        echo "  Title: " . substr($item['title'], 0, 60) . "...\n";
        echo "  Type: {$item['type']}\n";
        echo "  Is Vignette: " . ($item['is_vignette'] ? 'YES' : 'NO') . "\n";
        echo "  Question Count: {$item['question_count']}\n";

        $totalQuestions += $item['question_count'];
        if ($item['is_vignette']) {
            $vignetteCount++;
        } else {
            $regularCount++;
        }

        // Show questions for each item
        if ($item['question_count'] > 0) {
            $stmt2 = $pdo->prepare("
                SELECT q.id, q.hash, q.question
                FROM questions q
                WHERE q.item_id = ?
                ORDER BY q.id
            ");
            $stmt2->execute([$item['id']]);
            $questions = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            foreach ($questions as $qIndex => $question) {
                echo "    Question " . ($qIndex + 1) . ": ID {$question['id']}, Hash {$question['hash']}\n";
                echo "      Preview: " . substr(strip_tags($question['question']), 0, 50) . "...\n";
            }
        }
        echo "\n";
    }

    // Step 3: Summary comparison
    echo "=== STEP 3: SUMMARY COMPARISON ===\n";
    echo "Delivery 22 Claims:\n";
    echo "  - Total Items: {$delivery['total_items']}\n";
    echo "  - Total Questions: {$delivery['total_questions']}\n\n";

    echo "Actual Exam $examId Contains:\n";
    echo "  - Total Items: $totalItems\n";
    echo "  - Total Questions: $totalQuestions\n";
    echo "  - Vignette Items: $vignetteCount\n";
    echo "  - Regular Items: $regularCount\n\n";

    // Step 4: Identify discrepancies
    echo "=== STEP 4: DISCREPANCY ANALYSIS ===\n";
    $itemDiscrepancy = $totalItems - $delivery['total_items'];
    $questionDiscrepancy = $totalQuestions - $delivery['total_questions'];

    if ($itemDiscrepancy !== 0) {
        echo "⚠️  ITEM COUNT DISCREPANCY: " . ($itemDiscrepancy > 0 ? '+' : '') . "$itemDiscrepancy items\n";
    }

    if ($questionDiscrepancy !== 0) {
        echo "⚠️  QUESTION COUNT DISCREPANCY: " . ($questionDiscrepancy > 0 ? '+' : '') . "$questionDiscrepancy questions\n";
    }

    if ($itemDiscrepancy === 0 && $questionDiscrepancy === 0) {
        echo "✅ No discrepancies found - counts match perfectly\n";
    }

    // Step 5: Check if this should be 60 items
    echo "\n=== STEP 5: 60 ITEMS VERIFICATION ===\n";
    if ($totalItems !== 60) {
        echo "⚠️  Exam has $totalItems items, but expected 60 items\n";
        echo "   Difference: " . ($totalItems - 60) . " items\n";
    } else {
        echo "✅ Exam has exactly 60 items as expected\n";
    }

    if ($totalQuestions !== 60) {
        echo "⚠️  Exam has $totalQuestions questions, but expected 60 questions\n";
        echo "   Difference: " . ($totalQuestions - 60) . " questions\n";
    } else {
        echo "✅ Exam has exactly 60 questions as expected\n";
    }

    // Step 6: Check snapshot data
    echo "\n=== STEP 6: SNAPSHOT VERIFICATION ===\n";
    $stmt = $pdo->prepare("
        SELECT snapshot, total_items, total_questions
        FROM deliveries
        WHERE id = 22
    ");
    $stmt->execute();
    $deliverySnapshot = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($deliverySnapshot && $deliverySnapshot['snapshot']) {
        echo "✅ Snapshot found in delivery\n";
        echo "  Snapshot Items: {$deliverySnapshot['total_items']}\n";
        echo "  Snapshot Questions: {$deliverySnapshot['total_questions']}\n";

        $snapshot = json_decode($deliverySnapshot['snapshot'], true);
        if ($snapshot && isset($snapshot['exam_structure']['items'])) {
            $snapshotItems = count($snapshot['exam_structure']['items']);
            $snapshotQuestions = 0;

            foreach ($snapshot['exam_structure']['items'] as $snapshotItem) {
                $snapshotQuestions += count($snapshotItem['questions'] ?? []);
            }

            echo "  Actual Snapshot Items: $snapshotItems\n";
            echo "  Actual Snapshot Questions: $snapshotQuestions\n\n";

            // Compare snapshot with actual exam
            if ($snapshotItems !== $totalItems) {
                echo "⚠️  Snapshot vs Actual Items: $snapshotItems vs $totalItems\n";
            }
            if ($snapshotQuestions !== $totalQuestions) {
                echo "⚠️  Snapshot vs Actual Questions: $snapshotQuestions vs $totalQuestions\n";
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