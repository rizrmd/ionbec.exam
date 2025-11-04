<?php

/**
 * TEST PRODUCTION HASHES FROM LOGS
 * Test hash yang dipanggil frontend di production
 */

echo "=== TESTING PRODUCTION HASHES FROM LOGS ===\n\n";

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

    // Hash dari production logs yang dipanggil frontend
    $productionHashes = [
        '53gDGMky', // Vignette MCQ 9 & 10
        'XRClM4Ok', // Dari test sebelumnya
        'pwBeWwgQ', // Vignette MCQ 1 & 2
        'n0BPZAgL', // Regular item
        'dVg6X0Bp', // Regular item
        '3oKMJAB6', // Regular item
        'QnB0P4BA', // Regular item
    ];

    echo "=== TESTING PRODUCTION HASHES ===\n";

    foreach ($productionHashes as $hash) {
        echo "\nTesting hash: $hash\n";
        echo "------------------------\n";

        // Test 1: Find item by hash
        $stmt = $pdo->prepare("
            SELECT id, title, hash, is_vignette, content, type
            FROM items
            WHERE hash = ?
        ");
        $stmt->execute([$hash]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            echo "✅ Item found: ID {$item['id']}\n";
            echo "   Title: " . substr($item['title'], 0, 60) . "...\n";
            echo "   Is Vignette: " . ($item['is_vignette'] ? 'YES' : 'NO') . "\n";
            echo "   Type: {$item['type']}\n";

            // Test 2: Get questions for this item
            $stmt = $pdo->prepare("
                SELECT q.id, q.hash, q.question,
                       COUNT(a.id) as answer_count
                FROM questions q
                LEFT JOIN answers a ON q.id = a.question_id
                WHERE q.item_id = ?
                GROUP BY q.id, q.hash, q.question
                ORDER BY q.id
            ");
            $stmt->execute([$item['id']]);
            $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo "   Questions found: " . count($questions) . "\n";

            if (count($questions) > 0) {
                echo "   Sample questions:\n";
                foreach ($questions as $index => $question) {
                    if ($index < 3) { // Show first 3 questions
                        echo "     " . ($index + 1) . ". ID: {$question['id']}\n";
                        echo "        Hash: {$question['hash']}\n";
                        echo "        Preview: " . substr(strip_tags($question['question']), 0, 60) . "...\n";
                        echo "        Answers: {$question['answer_count']}\n";
                    }
                }

                // Test 3: Build API response (like getQuestions endpoint)
                $stmt = $pdo->prepare("
                    SELECT q.id, q.hash, q.question,
                           a.id as answer_id, a.answer, a.is_correct_answer
                    FROM questions q
                    LEFT JOIN answers a ON q.id = a.question_id
                    WHERE q.item_id = ?
                    ORDER BY q.id, a.id
                ");
                $stmt->execute([$item['id']]);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Group by question
                $apiQuestions = [];
                foreach ($results as $row) {
                    $questionId = $row['id'];
                    if (!isset($apiQuestions[$questionId])) {
                        $apiQuestions[$questionId] = [
                            'id' => $row['id'],
                            'hash' => $row['hash'],
                            'question' => $row['question'],
                            'answers' => []
                        ];
                    }
                    if ($row['answer_id']) {
                        $apiQuestions[$questionId]['answers'][] = [
                            'id' => $row['answer_id'],
                            'answer' => $row['answer'],
                            'is_correct_answer' => $row['is_correct_answer']
                        ];
                    }
                }

                // Hide correct answers (like production)
                foreach ($apiQuestions as &$question) {
                    foreach ($question['answers'] as &$answer) {
                        unset($answer['is_correct_answer']);
                    }
                }

                $apiResponse = [
                    'questions' => array_values($apiQuestions),
                    'attempt' => null
                ];

                echo "   ✅ API Response: " . count($apiResponse['questions']) . " questions, " . strlen(json_encode($apiResponse)) . " bytes\n";

                // Validate structure
                $validStructure = true;
                foreach ($apiResponse['questions'] as $question) {
                    if (!isset($question['id']) || !isset($question['question']) || !isset($question['answers'])) {
                        $validStructure = false;
                        break;
                    }
                }

                echo "   Structure Valid: " . ($validStructure ? 'YES' : 'NO') . "\n";

                // Test 4: Check if item belongs to Exam 74
                $stmt = $pdo->prepare("
                    SELECT ei.order, ei.exam_id
                    FROM exam_item ei
                    WHERE ei.item_id = ? AND ei.exam_id = 74
                ");
                $stmt->execute([$item['id']]);
                $examLink = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($examLink) {
                    echo "   ✅ Belongs to Exam 74 (Order: {$examLink['order']})\n";
                } else {
                    echo "   ❌ NOT in Exam 74!\n";
                }

            } else {
                echo "   ❌ No questions found!\n";
            }

        } else {
            echo "❌ Item not found with hash: $hash\n";
        }
    }

    // Check which exam these hashes actually belong to
    echo "\n=== CHECKING EXAM OWNERSHIP ===\n";
    $stmt = $pdo->prepare("
        SELECT ei.exam_id, ei.item_id, ei.order,
               e.name as exam_name,
               i.title, i.hash, i.is_vignette
        FROM exam_item ei
        JOIN items i ON ei.item_id = i.id
        JOIN exams e ON ei.exam_id = e.id
        WHERE i.hash IN ('" . implode("','", $productionHashes) . "')
        ORDER BY ei.exam_id, ei.order
    ");
    $stmt->execute();
    $hashOwnership = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($hashOwnership as $row) {
        $examId = $row['exam_id'];
        $examName = substr($row['exam_name'], 0, 30);
        $itemHash = $row['hash'];
        $itemTitle = substr($row['title'], 0, 40);
        $isVignette = $row['is_vignette'] ? 'V' : 'R';

        echo "Hash $itemHash: Exam $examId ($examName) | $itemTitle | $isVignette\n";
    }

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";