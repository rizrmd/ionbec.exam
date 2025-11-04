<?php

/**
 * TEST FRONTEND FLOW - SEQUENTIAL VALIDATION
 * Test apakah 55 pertanyaan bisa di-load dari backend ke frontend
 */

echo "=== TESTING FRONTEND FLOW - 55 QUESTIONS ===\n\n";

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

    // STEP 1: Validate delivery data
    echo "=== STEP 1: DELIVERY DATA VALIDATION ===\n";
    $stmt = $pdo->prepare("
        SELECT d.id, d.name, d.exam_id, d.duration,
               e.name as exam_name
        FROM deliveries d
        JOIN exams e ON d.exam_id = e.id
        WHERE d.id = 22
    ");
    $stmt->execute();
    $delivery = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "✅ Delivery 22 found: {$delivery['name']}\n";
    echo "   Using Exam {$delivery['exam_id']}: {$delivery['exam_name']}\n\n";

    // STEP 2: Get all exam items (like frontend would receive)
    echo "=== STEP 2: EXAM ITEMS RETRIEVAL ===\n";
    $stmt = $pdo->prepare("
        SELECT ei.order, ei.item_id,
               i.id, i.title, i.hash, i.is_vignette, i.content, i.type,
               COUNT(q.id) as question_count
        FROM exam_item ei
        JOIN items i ON ei.item_id = i.id
        LEFT JOIN questions q ON i.id = q.item_id
        WHERE ei.exam_id = ?
        GROUP BY ei.order, ei.item_id, i.id, i.title, i.hash, i.is_vignette, i.content, i.type
        ORDER BY ei.order
    ");
    $stmt->execute([$delivery['exam_id']]);
    $examItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "✅ Retrieved " . count($examItems) . " items from exam\n\n";

    // STEP 3: Test each item's questions (simulate frontend navigation)
    echo "=== STEP 3: INDIVIDUAL ITEM QUESTION RETRIEVAL ===\n";
    $totalQuestionsTested = 0;
    $successCount = 0;
    $failCount = 0;
    $vignetteTestCount = 0;
    $regularTestCount = 0;

    foreach ($examItems as $index => $item) {
        $itemNum = $index + 1;
        $itemHash = $item['hash'];
        $isVignette = $item['is_vignette'];
        $expectedQuestions = $item['question_count'];

        echo "Testing Item #$itemNum (Hash: $itemHash) - " . ($isVignette ? 'VIGNETTE' : 'REGULAR') . "\n";

        // Simulate frontend API call: GET /exam/questions/{item_hash}
        try {
            // Find item by hash (like getQuestions endpoint)
            $stmt = $pdo->prepare("
                SELECT id, title, hash, is_vignette, content
                FROM items
                WHERE hash = ?
            ");
            $stmt->execute([$itemHash]);
            $foundItem = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$foundItem) {
                echo "  ❌ FAIL: Item not found by hash\n";
                $failCount++;
                continue;
            }

            echo "  ✅ Item found by hash: ID {$foundItem['id']}\n";

            // Get questions for this item
            $stmt = $pdo->prepare("
                SELECT q.id, q.hash, q.question,
                       a.id as answer_id, a.answer, a.is_correct_answer
                FROM questions q
                LEFT JOIN answers a ON q.id = a.question_id
                WHERE q.item_id = ?
                ORDER BY q.id, a.id
            ");
            $stmt->execute([$foundItem['id']]);
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

            $actualQuestions = count($questions);
            $totalQuestionsTested += $actualQuestions;

            if ($isVignette) {
                $vignetteTestCount++;
            } else {
                $regularTestCount++;
            }

            // Validate question count
            if ($actualQuestions !== $expectedQuestions) {
                echo "  ⚠️  WARNING: Expected $expectedQuestions questions, found $actualQuestions\n";
            }

            // Validate each question has content and answers
            $validQuestions = 0;
            foreach ($questions as $question) {
                if (!empty($question['question']) && !empty($question['answers'])) {
                    $validQuestions++;
                }
            }

            if ($validQuestions === $actualQuestions && $actualQuestions > 0) {
                echo "  ✅ SUCCESS: $actualQuestions valid questions with answers\n";
                $successCount++;

                // Show sample for first few items
                if ($itemNum <= 3) {
                    echo "    Sample Question " . ($itemNum) . ": " . substr(strip_tags($questions[array_key_first($questions)]['question']), 0, 50) . "...\n";
                    echo "    Answers: " . count($questions[array_key_first($questions)]['answers']) . "\n";
                }
            } else {
                echo "  ❌ FAIL: $validQuestions/$actualQuestions valid questions\n";
                $failCount++;
            }

        } catch (Exception $e) {
            echo "  ❌ ERROR: " . $e->getMessage() . "\n";
            $failCount++;
        }

        echo "\n";
    }

    // STEP 4: Summary
    echo "=== STEP 4: FRONTEND FLOW SUMMARY ===\n";
    echo "Items Tested: " . count($examItems) . "\n";
    echo "✅ Successful Items: $successCount\n";
    echo "❌ Failed Items: $failCount\n";
    echo "Vignette Items Tested: $vignetteTestCount\n";
    echo "Regular Items Tested: $regularTestCount\n";
    echo "Total Questions Validated: $totalQuestionsTested\n\n";

    // STEP 5: Test complete API response structure
    echo "=== STEP 5: COMPLETE API RESPONSE SIMULATION ===\n";

    // Pick a vignette item for full testing
    $vignetteItem = null;
    foreach ($examItems as $item) {
        if ($item['is_vignette']) {
            $vignetteItem = $item;
            break;
        }
    }

    if ($vignetteItem) {
        echo "Testing full API response for vignette: {$vignetteItem['hash']}\n";

        // Simulate complete getQuestions endpoint
        $stmt = $pdo->prepare("
            SELECT id, title, hash, is_vignette, content
            FROM items
            WHERE hash = ?
        ");
        $stmt->execute([$vignetteItem['hash']]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
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

            // Build questions array
            $questions = [];
            foreach ($results as $row) {
                $questionId = $row['id'];
                if (!isset($questions[$questionId])) {
                    $questions[$questionId] = [
                        'id' => $row['id'],
                        'hash' => $row['hash'],
                        'question' => $row['question'],
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

            // Hide correct answers (like production)
            foreach ($questions as &$question) {
                foreach ($question['answers'] as &$answer) {
                    unset($answer['is_correct_answer']);
                }
            }

            $apiResponse = [
                'questions' => array_values($questions),
                'attempt' => null
            ];

            echo "✅ API Response built successfully\n";
            echo "   Response size: " . strlen(json_encode($apiResponse)) . " bytes\n";
            echo "   Questions in response: " . count($apiResponse['questions']) . "\n";
            echo "   JSON valid: " . (json_encode($apiResponse) !== false ? 'YES' : 'NO') . "\n";

            // Validate structure for frontend consumption
            $structureValid = true;
            foreach ($apiResponse['questions'] as $question) {
                if (!isset($question['id']) || !isset($question['question']) || !isset($question['answers'])) {
                    $structureValid = false;
                    break;
                }
            }

            echo "   Frontend-ready structure: " . ($structureValid ? 'YES' : 'NO') . "\n\n";
        }
    }

    // STEP 6: Final Conclusion
    echo "=== STEP 6: FRONTEND READINESS CONCLUSION ===\n";

    if ($successCount === count($examItems) && $totalQuestionsTested === 55) {
        echo "🎉 EXCELLENT: All 55 questions are ready for frontend!\n";
        echo "✅ Backend can serve all questions correctly\n";
        echo "✅ API response structure is valid\n";
        echo "✅ All questions have answers\n";
        echo "✅ Hash-based lookup works for all items\n\n";

        echo "If frontend still shows blank, the issue is:\n";
        echo "1. Frontend hash extraction from URL\n";
        echo "2. Frontend API call execution\n";
        echo "3. Frontend data binding/reactivity\n";
        echo "4. Frontend template rendering\n";

    } else {
        echo "⚠️  ISSUES FOUND:\n";
        echo "- Successful items: $successCount/" . count($examItems) . "\n";
        echo "- Questions validated: $totalQuestionsTested/55\n";
        echo "- Failed items: $failCount\n\n";

        echo "Backend issues need to be fixed before frontend can work.\n";
    }

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Script error: " . $e->getMessage() . "\n";
}

echo "\n=== FRONTEND FLOW TEST COMPLETE ===\n";