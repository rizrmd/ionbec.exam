<?php

/**
 * DEBUG VIGNETTE QUESTIONS FOR DELIVERY 22
 */

echo "=== DEBUGGING VIGNETTE QUESTIONS ===\n\n";

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

    // Check all items in delivery 22
    echo "=== ALL ITEMS IN DELIVERY 22 ===\n";
    $stmt = $pdo->prepare("
        SELECT i.id, i.title, i.hash, i.is_vignette, i.content,
               COUNT(q.id) as question_count
        FROM items i
        LEFT JOIN questions q ON i.id = q.item_id
        WHERE i.delivery_id = 22
        GROUP BY i.id, i.title, i.hash, i.is_vignette, i.content
        ORDER BY i.id
    ");
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as $item) {
        echo "Item ID: {$item['id']}\n";
        echo "Hash: {$item['hash']}\n";
        echo "Title: " . substr($item['title'], 0, 50) . "...\n";
        echo "Is Vignette: " . ($item['is_vignette'] ? 'YES' : 'NO') . "\n";
        echo "Question Count: {$item['question_count']}\n";

        if ($item['is_vignette']) {
            echo "Vignette Content (first 200 chars): " . substr(strip_tags($item['content']), 0, 200) . "...\n";

            // Get questions for this vignette item
            $stmt2 = $pdo->prepare("
                SELECT q.id, q.hash, q.question, q.item_hash,
                       COUNT(a.id) as answer_count
                FROM questions q
                LEFT JOIN answers a ON q.id = a.question_id
                WHERE q.item_id = ?
                GROUP BY q.id, q.hash, q.question, q.item_hash
                ORDER BY q.id
            ");
            $stmt2->execute([$item['id']]);
            $questions = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            echo "Questions in vignette:\n";
            foreach ($questions as $index => $question) {
                echo "  " . ($index + 1) . ". Question Hash: {$question['hash']}\n";
                echo "     Item Hash: " . ($question['item_hash'] ?: 'NULL') . "\n";
                echo "     Question Text: " . substr(strip_tags($question['question']), 0, 100) . "...\n";
                echo "     Answer Count: {$question['answer_count']}\n";
            }
        }
        echo "\n" . str_repeat("-", 80) . "\n\n";
    }

    // Check if there are any questions with missing item_hash
    echo "=== QUESTIONS WITH MISSING ITEM_HASH ===\n";
    $stmt = $pdo->prepare("
        SELECT q.id, q.hash, q.question, q.item_hash, i.title, i.is_vignette
        FROM questions q
        JOIN items i ON q.item_id = i.id
        WHERE i.delivery_id = 22 AND (q.item_hash IS NULL OR q.item_hash = '')
    ");
    $stmt->execute();
    $nullHashQuestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($nullHashQuestions) {
        foreach ($nullHashQuestions as $question) {
            echo "Question ID: {$question['id']}\n";
            echo "Hash: {$question['hash']}\n";
            echo "Item Hash: " . ($question['item_hash'] ?: 'NULL') . "\n";
            echo "Item Title: " . substr($question['title'], 0, 50) . "...\n";
            echo "Is Vignette Item: " . ($question['is_vignette'] ? 'YES' : 'NO') . "\n";
            echo "Question Text: " . substr(strip_tags($question['question']), 0, 100) . "...\n\n";
        }
    } else {
        echo "✅ All questions have item_hash populated\n\n";
    }

    // Check snapshot data for delivery 22
    echo "=== SNAPSHOT DATA FOR DELIVERY 22 ===\n";
    $stmt = $pdo->prepare("
        SELECT id, snapshot, total_items, total_questions
        FROM deliveries
        WHERE id = 22
    ");
    $stmt->execute();
    $delivery = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($delivery && $delivery['snapshot']) {
        echo "✅ Snapshot found\n";
        echo "Total Items: {$delivery['total_items']}\n";
        echo "Total Questions: {$delivery['total_questions']}\n";

        $snapshot = json_decode($delivery['snapshot'], true);
        if ($snapshot && isset($snapshot['exam_structure']['items'])) {
            echo "Snapshot items count: " . count($snapshot['exam_structure']['items']) . "\n";

            foreach ($snapshot['exam_structure']['items'] as $index => $snapshotItem) {
                if ($snapshotItem['is_vignette'] ?? false) {
                    echo "Vignette Item #" . ($index + 1) . ":\n";
                    echo "  Hash: {$snapshotItem['hash']}\n";
                    echo "  Title: " . substr($snapshotItem['title'], 0, 50) . "...\n";
                    echo "  Questions count: " . count($snapshotItem['questions'] ?? []) . "\n";

                    if (isset($snapshotItem['questions'])) {
                        foreach ($snapshotItem['questions'] as $qIndex => $question) {
                            echo "    Question " . ($qIndex + 1) . ": " . substr(strip_tags($question['question']), 0, 80) . "...\n";
                        }
                    }
                    echo "\n";
                }
            }
        }
    } else {
        echo "❌ No snapshot found for delivery 22\n\n";
    }

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";