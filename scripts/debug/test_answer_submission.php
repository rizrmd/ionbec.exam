<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "🧪 TESTING ANSWER SUBMISSION FOR DELIVERY 152\n";
echo "============================================\n\n";

try {
    // Get delivery and attempt details
    $delivery = DB::select('SELECT * FROM deliveries WHERE hash = ?', ['26EAx9r9'])[0];
    $attempt = DB::select('SELECT * FROM attempts WHERE delivery_id = 152 ORDER BY created_at DESC LIMIT 1')[0];

    echo "📦 Delivery Details:\n";
    echo "   ID: " . $delivery->id . "\n";
    echo "   Name: " . $delivery->name . "\n";
    echo "   Hash: " . $delivery->hash . "\n";
    echo "   Exam ID: " . $delivery->exam_id . "\n\n";

    echo "🎯 Attempt Details:\n";
    echo "   ID: " . $attempt->id . "\n";
    echo "   Hash: " . $attempt->hash . "\n";
    echo "   Current Score: " . $attempt->score . "\n";
    echo "   Current Progress: " . $attempt->progress . "%\n\n";

    // Get snapshot data
    $snapshot = DB::select('SELECT * FROM delivery_snapshots WHERE delivery_id = 152')[0];
    $structure = json_decode($snapshot->exam_structure, true);

    if ($structure && isset($structure['items'][0]['questions'][0])) {
        $sampleQuestion = $structure['items'][0]['questions'][0];
        $sampleAnswer = $sampleQuestion['answers'][0];

        echo "📝 Sample Question & Answer:\n";
        echo "   Question ID: " . $sampleQuestion['id'] . "\n";
        echo "   Question: " . substr(strip_tags($sampleQuestion['question']), 0, 50) . "...\n";
        echo "   Answer ID: " . $sampleAnswer['id'] . "\n";
        echo "   Answer: " . substr(strip_tags($sampleAnswer['answer']), 0, 30) . "...\n";
        echo "   Is Correct: " . ($sampleAnswer['is_correct_answer'] ? 'Yes' : 'No') . "\n";
        echo "   Score: " . $sampleQuestion['score'] . "\n\n";

        // Test 1: Simulate the MainController::answer() method
        echo "🔄 Testing MainController::answer() method:\n";

        // Mock the request data
        $requestData = [
            'attempt_hash' => $attempt->hash,
            'question_id' => $sampleQuestion['id'],
            'answer_id' => $sampleAnswer['id'],
            'answer' => $sampleAnswer['answer'],
        ];

        echo "   Request data:\n";
        foreach ($requestData as $key => $value) {
            echo "     - $key: " . (is_string($value) ? substr($value, 0, 30) . "..." : $value) . "\n";
        }
        echo "\n";

        // Step 1: Verify attempt exists and is not expired
        echo "   Step 1: Verifying attempt...\n";
        $timerService = app(\App\Services\ExamTimerService::class);
        $isExpired = $timerService->isAttemptExpired($attempt);
        echo "     Attempt expired: " . ($isExpired ? 'Yes' : 'No') . "\n";

        if ($isExpired) {
            echo "     ❌ Attempt is expired, cannot submit answer\n";
            exit;
        }

        // Step 2: Save answer to database
        echo "   Step 2: Saving answer to database...\n";

        try {
            $answerHash = \App\Models\Exams\Answer::idToHash($sampleAnswer['id']);
            $actualScore = $sampleAnswer['is_correct_answer'] ? $sampleQuestion['score'] : 0;

            echo "     Calculated answer hash: $answerHash\n";
            echo "     Actual score: $actualScore\n";

            // Check if answer already exists
            $existingAnswer = DB::select('SELECT * FROM attempt_question WHERE attempt_id = ? AND question_id = ?', [$attempt->id, $sampleQuestion['id']]);
            if (!empty($existingAnswer)) {
                echo "     ⚠️ Answer already exists, updating...\n";
            }

            // Insert/update answer
            $attemptQuestion = DB::select('
                INSERT INTO attempt_question (attempt_id, question_id, answer_id, answer_hash, answer, is_correct, score, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ON CONFLICT (attempt_id, question_id)
                DO UPDATE SET
                    answer_id = EXCLUDED.answer_id,
                    answer_hash = EXCLUDED.answer_hash,
                    answer = EXCLUDED.answer,
                    is_correct = EXCLUDED.is_correct,
                    score = EXCLUDED.score,
                    updated_at = NOW()
                RETURNING id, attempt_id, question_id, score, is_correct
            ', [
                $attempt->id,
                $sampleQuestion['id'],
                $sampleAnswer['id'],
                $answerHash,
                $sampleAnswer['answer'],
                $sampleAnswer['is_correct_answer'],
                $actualScore
            ]);

            if (!empty($attemptQuestion)) {
                $result = $attemptQuestion[0];
                echo "     ✅ Answer saved successfully!\n";
                echo "     - Record ID: " . $result->id . "\n";
                echo "     - Score: " . $result->score . "\n";
                echo "     - Is Correct: " . ($result->is_correct ? 'Yes' : 'No') . "\n";
            } else {
                echo "     ❌ Failed to save answer\n";
                exit;
            }

        } catch (Exception $e) {
            echo "     ❌ Error saving answer: " . $e->getMessage() . "\n";
            echo "     Trace: " . $e->getTraceAsString() . "\n";
            exit;
        }

        // Step 3: Calculate and update score
        echo "   Step 3: Calculating score...\n";

        try {
            // Get all answered questions for this attempt
            $answeredQuestions = DB::select('SELECT COUNT(*) as count FROM attempt_question WHERE attempt_id = ?', [$attempt->id]);
            $answeredCount = $answeredQuestions[0]->count;
            echo "     Answered questions: $answeredCount\n";

            if ($answeredCount > 0) {
                // Calculate total score
                $scoreResult = DB::select('SELECT SUM(score) as total_score, AVG(score) as avg_score FROM attempt_question WHERE attempt_id = ?', [$attempt->id]);
                $totalScore = $scoreResult[0]->total_score;
                $avgScore = $scoreResult[0]->avg_score;

                // Calculate progress (based on 4 total questions)
                $totalQuestions = 4;
                $progress = ceil($answeredCount * 100 / $totalQuestions);
                $progress = min($progress, 100);

                echo "     Total score: $totalScore\n";
                echo "     Average score: " . number_format($avgScore, 2) . "\n";
                echo "     Progress: $progress%\n";

                // Update attempt
                DB::update('UPDATE attempts SET score = ?, progress = ?, updated_at = NOW() WHERE id = ?', [$avgScore, $progress, $attempt->id]);

                echo "     ✅ Attempt updated!\n";
            } else {
                echo "     ⚠️ No answered questions found\n";
            }

        } catch (Exception $e) {
            echo "     ❌ Error calculating score: " . $e->getMessage() . "\n";
        }

        // Step 4: Verify final state
        echo "   Step 4: Verifying final state...\n";

        $finalAttempt = DB::select('SELECT * FROM attempts WHERE id = ?', [$attempt->id])[0];
        $finalAnswerCount = DB::select('SELECT COUNT(*) as count FROM attempt_question WHERE attempt_id = ?', [$attempt->id])[0]->count;

        echo "     Final score: " . $finalAttempt->score . "\n";
        echo "     Final progress: " . $finalAttempt->progress . "%\n";
        echo "     Total answer records: $finalAnswerCount\n";

        if ($finalAnswerCount > 0 && $finalAttempt->score > 0) {
            echo "     ✅ SUCCESS: Answer submission and scoring working!\n";
        } else {
            echo "     ❌ ISSUE: Score or progress still zero\n";
        }

    } else {
        echo "❌ No questions found in snapshot\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🎯 TEST COMPLETE\n";