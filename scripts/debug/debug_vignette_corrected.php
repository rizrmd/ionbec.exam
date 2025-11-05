<?php

/**
 * DEBUG VIGNETTE QUESTIONS FOR DELIVERY 22 - CORRECTED VERSION
 */

echo "=== DEBUGGING VIGNETTE QUESTIONS (CORRECTED) ===\n\n";

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

    // Check delivery 22 details first
    echo "=== DELIVERY 22 DETAILS ===\n";
    $stmt = $pdo->prepare("
        SELECT id, name, exam_id, automatic_start, duration, scheduled_at
        FROM deliveries
        WHERE id = 22
    ");
    $stmt->execute();
    $delivery = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($delivery) {
        echo "✅ Delivery 22 found\n";
        echo "Name: {$delivery['name']}\n";
        echo "Exam ID: {$delivery['exam_id']}\n";
        echo "Duration: {$delivery['duration']} minutes\n";
        echo "Auto Start: " . ($delivery['automatic_start'] ? 'YES' : 'NO') . "\n\n";
    } else {
        echo "❌ Delivery 22 not found\n\n";
        exit;
    }

    // Get all items for this exam using correct table name
    echo "=== EXAM ITEMS FOR EXAM {$delivery['exam_id']} ===\n";
    $stmt = $pdo->prepare("
        SELECT ei.exam_id, ei.item_id, ei.order,
               i.id, i.title, i.hash, i.is_vignette, i.content,
               COUNT(q.id) as question_count
        FROM exam_item ei
        JOIN items i ON ei.item_id = i.id
        LEFT JOIN questions q ON i.id = q.item_id
        WHERE ei.exam_id = ?
        GROUP BY ei.exam_id, ei.item_id, ei.order, i.id, i.title, i.hash, i.is_vignette, i.content
        ORDER BY ei.order
    ");
    $stmt->execute([$delivery['exam_id']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as $index => $item) {
        echo "Item #" . ($index + 1) . "\n";
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
            foreach ($questions as $qIndex => $question) {
                echo "  " . ($qIndex + 1) . ". Question ID: {$question['id']}\n";
                echo "     Question Hash: {$question['hash']}\n";
                echo "     Item Hash: " . ($question['item_hash'] ?: 'NULL') . "\n";
                echo "     Question Text: " . substr(strip_tags($question['question']), 0, 100) . "...\n";
                echo "     Answer Count: {$question['answer_count']}\n";

                // Get answers for each question
                if ($question['answer_count'] > 0) {
                    $stmt3 = $pdo->prepare("
                        SELECT id, answer, is_correct_answer
                        FROM answers
                        WHERE question_id = ?
                        ORDER BY id
                    ");
                    $stmt3->execute([$question['id']]);
                    $answers = $stmt3->fetchAll(PDO::FETCH_ASSOC);

                    echo "     Answers:\n";
                    foreach ($answers as $aIndex => $answer) {
                        $correct = $answer['is_correct_answer'] ? ' (CORRECT)' : '';
                        echo "       " . ($aIndex + 1) . ". " . substr(strip_tags($answer['answer']), 0, 50) . "...$correct\n";
                    }
                }
            }
        }
        echo "\n" . str_repeat("-", 80) . "\n\n";
    }

    // Check if there are any questions with missing item_hash
    echo "=== QUESTIONS WITH MISSING ITEM_HASH ===\n";
    $allItemIds = array_column($items, 'id');
    if (!empty($allItemIds)) {
        $placeholders = str_repeat('?,', count($allItemIds) - 1) . '?';
        $stmt = $pdo->prepare("
            SELECT q.id, q.hash, q.question, q.item_hash, i.title, i.is_vignette
            FROM questions q
            JOIN items i ON q.item_id = i.id
            WHERE q.item_id IN ($placeholders) AND (q.item_hash IS NULL OR q.item_hash = '')
        ");
        $stmt->execute($allItemIds);
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
    }

    // Test hash-based lookup like frontend does
    echo "=== TEST HASH-BASED LOOKUP (LIKE FRONTEND) ===\n";
    $vignetteItems = array_filter($items, function($item) {
        return $item['is_vignette'];
    });

    if (!empty($vignetteItems)) {
        $testVignette = reset($vignetteItems); // Get first vignette item
        echo "Testing with vignette item: {$testVignette['hash']}\n";

        // Test 1: Find item by hash (like frontend getQuestions)
        $stmt = $pdo->prepare("
            SELECT id, title, hash, is_vignette, content
            FROM items
            WHERE hash = ?
        ");
        $stmt->execute([$testVignette['hash']]);
        $itemByHash = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($itemByHash) {
            echo "✅ Item found by hash lookup: ID {$itemByHash['id']}\n";

            // Test 2: Get questions for this item (like getQuestions API)
            $stmt = $pdo->prepare("
                SELECT q.id, q.hash, q.question, q.item_hash,
                       a.id as answer_id, a.answer, a.is_correct_answer
                FROM questions q
                LEFT JOIN answers a ON q.id = a.question_id
                WHERE q.item_id = ?
                ORDER BY q.id, a.id
            ");
            $stmt->execute([$itemByHash['id']]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Group by question
            $questions = [];
            foreach ($results as $row) {
                $questionId = $row['id'];
                if (!isset($questions[$questionId])) {
                    $questions[$questionId] = [
                        'id' => $row['id'],
                        'hash' => $row['hash'],
                        'question' => $row['question'],
                        'item_hash' => $row['item_hash'],
                        'answers' => []
                    ];
                }
                if ($row['answer_id']) {
                    $questions[$questionId]['answers'][] = [
                        'id' => $row['answer_id'],
                        'answer' => $row['answer'],
                        'is_correct_answer' => $row['is_correct_answer']
                    ];
                }
            }

            echo "✅ Found " . count($questions) . " questions via hash lookup\n";

            // Test 3: Build API response (like getQuestions endpoint)
            $apiResponse = [
                'questions' => array_values($questions),
                'attempt' => null
            ];

            // Hide correct answers (like in production)
            foreach ($apiResponse['questions'] as &$question) {
                foreach ($question['answers'] as &$answer) {
                    unset($answer['is_correct_answer']);
                }
            }

            echo "✅ API response built successfully\n";
            echo "   Response size: " . strlen(json_encode($apiResponse)) . " bytes\n";
            echo "   Valid questions: " . count($apiResponse['questions']) . "\n";

            // Show sample question structure
            if (!empty($apiResponse['questions'])) {
                $sampleQuestion = $apiResponse['questions'][0];
                echo "   Sample question structure:\n";
                echo "     - ID: {$sampleQuestion['id']}\n";
                echo "     - Hash: {$sampleQuestion['hash']}\n";
                echo "     - Item Hash: " . ($sampleQuestion['item_hash'] ?: 'NULL') . "\n";
                echo "     - Question preview: " . substr(strip_tags($sampleQuestion['question']), 0, 50) . "...\n";
                echo "     - Answer count: " . count($sampleQuestion['answers']) . "\n";
            }

        } else {
            echo "❌ Item not found by hash lookup\n";
        }
    } else {
        echo "❌ No vignette items found to test\n";
    }

    // Check snapshot data
    echo "\n=== SNAPSHOT DATA FOR DELIVERY 22 ===\n";
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
                            echo "    Item Hash: " . ($question['item_hash'] ?? 'NULL') . "\n";
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