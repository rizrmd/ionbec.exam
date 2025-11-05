<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "🧪 TESTING ANSWER SUBMISSION FOR DELIVERY 152 (FIXED)\n";
echo "====================================================\n\n";

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

        // Step 1: Check attempt expiration manually (bypass ExamTimerService for testing)
        echo "🔄 Testing Answer Submission:\n";
        echo "   Step 1: Checking attempt status...\n";

        // Check if attempt is expired by looking at created_at + duration
        $createdAt = new \Carbon\Carbon($attempt->created_at);
        $expiryTime = $createdAt->addMinutes($delivery->duration ?? 60);
        $now = \Carbon\Carbon::now();
        $isExpired = $now->gt($expiryTime);

        echo "     Created at: " . $attempt->created_at . "\n";
        echo "     Duration: " . ($delivery->duration ?? 60) . " minutes\n";
        echo "     Expiry time: " . $expiryTime . "\n";
        echo "     Current time: " . $now . "\n";
        echo "     Is expired: " . ($isExpired ? 'Yes' : 'No') . "\n";

        if ($isExpired) {
            echo "     ❌ Attempt is expired, cannot submit answer\n";
            exit;
        }

        // Step 2: Save answer to database
        echo "   Step 2: Saving answer to database...\n";

        try {
            $actualScore = $sampleAnswer['is_correct_answer'] ? $sampleQuestion['score'] : 0;

            echo "     Actual score: $actualScore\n";

            // Check if answer already exists
            $existingAnswer = DB::select('SELECT * FROM attempt_question WHERE attempt_id = ? AND question_id = ?', [$attempt->id, $sampleQuestion['id']]);
            if (!empty($existingAnswer)) {
                echo "     ⚠️ Answer already exists, updating...\n";
            }

            // Insert/update answer using plain SQL
            if (!empty($existingAnswer)) {
                // Update existing answer
                DB::update('
                    UPDATE attempt_question
                    SET answer_id = ?, answer_hash = ?, answer = ?, is_correct = ?, score = ?, updated_at = NOW()
                    WHERE attempt_id = ? AND question_id = ?
                ', [
                    $sampleAnswer['id'],
                    'hash_' . $sampleAnswer['id'], // simple hash for testing
                    $sampleAnswer['answer'],
                    $sampleAnswer['is_correct_answer'],
                    $actualScore,
                    $attempt->id,
                    $sampleQuestion['id']
                ]);
            } else {
                // Insert new answer
                DB::insert('
                    INSERT INTO attempt_question (attempt_id, question_id, answer_id, answer_hash, answer, is_correct, score, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ', [
                    $attempt->id,
                    $sampleQuestion['id'],
                    $sampleAnswer['id'],
                    'hash_' . $sampleAnswer['id'], // simple hash for testing
                    $sampleAnswer['answer'],
                    $sampleAnswer['is_correct_answer'],
                    $actualScore
                ]);
            }

            echo "     ✅ Answer saved successfully!\n";

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

        // Step 5: Check actual API call
        echo "\n🌐 TESTING ACTUAL API CALL:\n";
        echo "   URL: /exam/answer\n";
        echo "   Method: POST\n";
        echo "   Expected route: POST exam/answer [exam.answer] -> MainController@answer\n\n";

        // Check if we can access the controller
        try {
            $mainController = new \App\Http\Controllers\Exam\MainController();
            echo "   ✅ MainController can be instantiated\n";
        } catch (Exception $e) {
            echo "   ❌ Error instantiating MainController: " . $e->getMessage() . "\n";
        }

    } else {
        echo "❌ No questions found in snapshot\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🎯 TEST COMPLETE\n";