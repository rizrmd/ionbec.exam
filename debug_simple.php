<?php

// Use environment variables directly
$host = '127.0.0.1';
$port = '3306';
$dbname = 'ionbec-exam-local';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== DEBUG QUESTION BLANK ISSUE ===\n\n";

    // Get a sample delivery
    $stmt = $pdo->query("SELECT id, exam_id, client_id, snapshot FROM deliveries LIMIT 1");
    $delivery = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$delivery) {
        echo "No delivery found!\n";
        exit;
    }

    echo "Delivery ID: {$delivery['id']}\n";
    echo "Exam ID: {$delivery['exam_id']}\n";
    echo "Client ID: {$delivery['client_id']}\n";
    echo "Has snapshot: " . ($delivery['snapshot'] ? 'YES' : 'NO') . "\n\n";

    // Check exam
    $stmt = $pdo->prepare("SELECT id, name FROM exams WHERE id = ?");
    $stmt->execute([$delivery['exam_id']]);
    $exam = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$exam) {
        echo "Exam not found!\n";
        exit;
    }

    echo "Exam found: {$exam['name']} (ID: {$exam['id']})\n\n";

    // Check snapshot data
    if ($delivery['snapshot']) {
        $snapshot = json_decode($delivery['snapshot'], true);
        if ($snapshot) {
            echo "Snapshot decoded successfully!\n";
            echo "Total questions in snapshot: " . ($snapshot['total_questions'] ?? 'N/A') . "\n";
            echo "Total items in snapshot: " . ($snapshot['total_items'] ?? 'N/A') . "\n";

            if (isset($snapshot['exam_structure']['items'])) {
                echo "Items in snapshot: " . count($snapshot['exam_structure']['items']) . "\n";

                $firstItem = $snapshot['exam_structure']['items'][0] ?? null;
                if ($firstItem) {
                    echo "First item ID: {$firstItem['id']}\n";
                    echo "First item has questions: " . (isset($firstItem['questions']) ? 'YES' : 'NO') . "\n";

                    if (isset($firstItem['questions']) && !empty($firstItem['questions'])) {
                        echo "Questions count in first item: " . count($firstItem['questions']) . "\n";

                        $firstQuestion = $firstItem['questions'][0] ?? null;
                        if ($firstQuestion) {
                            echo "First question ID: {$firstQuestion['id']}\n";
                            echo "First question type: " . ($firstQuestion['type']['name'] ?? 'unknown') . "\n";
                            echo "First question has answers: " . (isset($firstQuestion['answers']) ? 'YES' : 'NO') . "\n";

                            if (isset($firstQuestion['answers'])) {
                                echo "Answers count: " . count($firstQuestion['answers']) . "\n";

                                // Check first answer
                                $firstAnswer = $firstQuestion['answers'][0] ?? null;
                                if ($firstAnswer) {
                                    echo "First answer ID: {$firstAnswer['id']}\n";
                                    echo "First answer text: " . substr($firstAnswer['answer'] ?? '', 0, 50) . "...\n";
                                    echo "First answer is correct: " . ($firstAnswer['is_correct_answer'] ?? 'unknown') . "\n";
                                }
                            }
                        }
                    }
                }
            } else {
                echo "No exam_structure[items] in snapshot!\n";
            }
        } else {
            echo "Failed to decode snapshot JSON!\n";
        }
    } else {
        echo "No snapshot found - checking database directly...\n\n";

        // Check items directly
        $stmt = $pdo->prepare("
            SELECT i.id, i.is_vignette, COUNT(DISTINCT q.id) as question_count
            FROM items i
            JOIN exam_item ei ON i.id = ei.item_id
            LEFT JOIN questions q ON i.id = q.item_id
            WHERE ei.exam_id = ?
            GROUP BY i.id, i.is_vignette
            ORDER BY ei.order
            LIMIT 5
        ");
        $stmt->execute([$delivery['exam_id']]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "Items count: " . count($items) . "\n";

        if (!empty($items)) {
            $firstItem = $items[0];
            echo "First item ID: {$firstItem['id']}\n";
            echo "First item is vignette: " . ($firstItem['is_vignette'] ? 'YES' : 'NO') . "\n";
            echo "Questions count: {$firstItem['question_count']}\n";

            // Get questions for first item
            $stmt = $pdo->prepare("
                SELECT q.id, q.question, qt.name as type_name
                FROM questions q
                JOIN question_types qt ON q.type_id = qt.id
                WHERE q.item_id = ?
                LIMIT 3
            ");
            $stmt->execute([$firstItem['id']]);
            $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($questions)) {
                $firstQuestion = $questions[0];
                echo "First question ID: {$firstQuestion['id']}\n";
                echo "First question type: {$firstQuestion['type_name']}\n";
                echo "First question text: " . substr(strip_tags($firstQuestion['question']), 0, 100) . "...\n";

                // Get answers for first question
                $stmt = $pdo->prepare("
                    SELECT a.id, a.answer, a.is_correct_answer
                    FROM answers a
                    WHERE a.question_id = ?
                    ORDER BY a.order
                ");
                $stmt->execute([$firstQuestion['id']]);
                $answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo "Answers count: " . count($answers) . "\n";

                if (!empty($answers)) {
                    $firstAnswer = $answers[0];
                    echo "First answer ID: {$firstAnswer['id']}\n";
                    echo "First answer text: " . substr(strip_tags($firstAnswer['answer']), 0, 50) . "...\n";
                    echo "First answer is correct: " . ($firstAnswer['is_correct_answer'] ? 'YES' : 'NO') . "\n";
                }
            }
        }
    }

    echo "\n=== End Debug ===\n";

} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}