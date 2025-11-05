<?php

/**
 * CHECK DELIVERY QUESTIONS DUPLICATION
 * Investigate why delivery j3GlgkLD shows 7 questions instead of 4
 */

echo "=== CHECKING DELIVERY QUESTIONS DUPLICATION ===\n\n";

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

    // Find delivery j3GlgkLD
    echo "=== FINDING DELIVERY j3GlgkLD ===\n";
    $stmt = $pdo->prepare("SELECT id, name, hash, exam_id, group_id FROM deliveries WHERE MD5(CONCAT(id::text, 'ionbec')) = ? OR hash = ?");
    $stmt->execute(['j3GlgkLD', 'j3GlgkLD']);
    $delivery = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$delivery) {
        echo "❌ Delivery j3GlgkLD not found\n";
        exit;
    }

    echo "✅ Found delivery:\n";
    echo "  ID: {$delivery['id']}\n";
    echo "  Name: {$delivery['name']}\n";
    echo "  Hash: {$delivery['hash']}\n";
    echo "  Exam ID: {$delivery['exam_id']}\n";
    echo "  Group ID: {$delivery['group_id']}\n\n";

    // Get exam details
    echo "=== EXAM DETAILS ===\n";
    $stmt = $pdo->prepare("SELECT id, title, is_interview FROM exams WHERE id = ?");
    $stmt->execute([$delivery['exam_id']]);
    $exam = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($exam) {
        echo "  Exam ID: {$exam['id']}\n";
        echo "  Title: {$exam['title']}\n";
        echo "  Is Interview: " . ($exam['is_interview'] ? 'YES' : 'NO') . "\n\n";
    }

    // Check snapshot data
    echo "=== DELIVERY SNAPSHOT ===\n";
    $stmt = $pdo->prepare("SELECT total_items, total_questions, exam_structure FROM delivery_snapshots WHERE delivery_id = ?");
    $stmt->execute([$delivery['id']]);
    $snapshot = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($snapshot) {
        echo "  Total Items (Snapshot): {$snapshot['total_items']}\n";
        echo "  Total Questions (Snapshot): {$snapshot['total_questions']}\n\n";

        $examStructure = json_decode($snapshot['exam_structure'], true);
        if ($examStructure && isset($examStructure['items'])) {
            echo "=== ITEMS IN SNAPSHOT ===\n";
            foreach ($examStructure['items'] as $index => $item) {
                echo "  Item " . ($index + 1) . ": " . ($item['title'] ?? 'No title') . "\n";
                if (isset($item['questions']) && is_array($item['questions'])) {
                    echo "    Questions: " . count($item['questions']) . "\n";
                    foreach ($item['questions'] as $qIndex => $question) {
                        echo "      Q" . ($qIndex + 1) . ": " . substr($question['question'] ?? 'No question', 0, 50) . "...\n";
                    }
                }
                echo "\n";
            }
        }
    } else {
        echo "  ❌ No snapshot found\n\n";
    }

    // Check current exam-item relationships
    echo "=== CURRENT EXAM-ITEM RELATIONSHIPS ===\n";
    $stmt = $pdo->prepare("
        SELECT i.id, i.title, ei.order
        FROM items i
        JOIN exam_item ei ON i.id = ei.item_id
        WHERE ei.exam_id = ?
        ORDER BY ei.order
    ");
    $stmt->execute([$delivery['exam_id']]);
    $currentItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "  Current items in exam: " . count($currentItems) . "\n";
    foreach ($currentItems as $index => $item) {
        echo "    Item " . ($index + 1) . " (Order: {$item['order']}): {$item['title']}\n";
    }
    echo "\n";

    // Check questions for each item
    echo "=== QUESTIONS PER ITEM ===\n";
    $totalQuestions = 0;
    foreach ($currentItems as $item) {
        $stmt = $pdo->prepare("SELECT id, question FROM questions WHERE item_id = ?");
        $stmt->execute([$item['id']]);
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "  Item: {$item['title']}\n";
        echo "    Questions: " . count($questions) . "\n";
        $totalQuestions += count($questions);

        foreach ($questions as $qIndex => $question) {
            echo "      Q" . ($qIndex + 1) . " (ID: {$question['id']}): " . substr($question['question'], 0, 50) . "...\n";
        }
        echo "\n";
    }

    echo "=== SUMMARY ===\n";
    echo "  Total Items (Current): " . count($currentItems) . "\n";
    echo "  Total Questions (Current): $totalQuestions\n";
    echo "  Total Items (Snapshot): " . ($snapshot['total_items'] ?? 'N/A') . "\n";
    echo "  Total Questions (Snapshot): " . ($snapshot['total_questions'] ?? 'N/A') . "\n\n";

    // Check for potential duplication causes
    echo "=== DUPLICATION ANALYSIS ===\n";

    // Check if there are duplicate question IDs in the snapshot
    if ($snapshot && isset($examStructure['items'])) {
        $allQuestionIds = [];
        $duplicates = [];

        foreach ($examStructure['items'] as $item) {
            if (isset($item['questions']) && is_array($item['questions'])) {
                foreach ($item['questions'] as $question) {
                    $qId = $question['id'] ?? null;
                    if ($qId) {
                        if (isset($allQuestionIds[$qId])) {
                            $duplicates[] = $qId;
                        } else {
                            $allQuestionIds[$qId] = true;
                        }
                    }
                }
            }
        }

        if (!empty($duplicates)) {
            echo "  ❌ DUPLICATE QUESTION IDs FOUND IN SNAPSHOT: " . implode(', ', array_unique($duplicates)) . "\n";
        } else {
            echo "  ✅ No duplicate question IDs in snapshot\n";
        }
    }

    // Check current database for duplicates
    $stmt = $pdo->prepare("
        SELECT q.id, q.question, COUNT(*) as count
        FROM questions q
        JOIN exam_item ei ON q.item_id = ei.item_id
        WHERE ei.exam_id = ?
        GROUP BY q.id, q.question
        HAVING COUNT(*) > 1
    ");
    $stmt->execute([$delivery['exam_id']]);
    $dbDuplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($dbDuplicates)) {
        echo "  ❌ DUPLICATE QUESTIONS FOUND IN DATABASE:\n";
        foreach ($dbDuplicates as $dup) {
            echo "    Question ID {$dup['id']}: " . substr($dup['question'], 0, 50) . "... (Count: {$dup['count']})\n";
        }
    } else {
        echo "  ✅ No duplicate questions in current database\n";
    }

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== DELIVERY QUESTIONS CHECKING COMPLETE ===\n";