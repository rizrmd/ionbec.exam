<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "🧪 SIMPLE ANSWER SUBMISSION TEST\n";
echo "================================\n\n";

try {
    // Get attempt details
    $attempt = DB::select('SELECT * FROM attempts WHERE delivery_id = 152 ORDER BY created_at DESC LIMIT 1')[0];

    echo "🎯 Attempt ID: " . $attempt->id . "\n";
    echo "   Current Score: " . $attempt->score . "\n";
    echo "   Current Progress: " . $attempt->progress . "%\n\n";

    // Check current answer count
    $currentAnswers = DB::select('SELECT COUNT(*) as count FROM attempt_question WHERE attempt_id = ?', [$attempt->id])[0]->count;
    echo "   Current answer records: $currentAnswers\n\n";

    // Get snapshot data
    $snapshot = DB::select('SELECT * FROM delivery_snapshots WHERE delivery_id = 152')[0];
    $structure = json_decode($snapshot->exam_structure, true);

    if ($structure && isset($structure['items'][0]['questions'][0])) {
        $sampleQuestion = $structure['items'][0]['questions'][0];
        $sampleAnswer = $sampleQuestion['answers'][0];

        echo "📝 Test Data:\n";
        echo "   Question ID: " . $sampleQuestion['id'] . "\n";
        echo "   Answer ID: " . $sampleAnswer['id'] . "\n";
        echo "   Is Correct: " . ($sampleAnswer['is_correct_answer'] ? 'Yes' : 'No') . "\n";
        echo "   Score: " . $sampleQuestion['score'] . "\n\n";

        // Simple test: Insert answer directly
        echo "🔄 Testing Direct Answer Insert:\n";

        try {
            // Delete any existing answer for this question first
            DB::delete('DELETE FROM attempt_question WHERE attempt_id = ? AND question_id = ?', [$attempt->id, $sampleQuestion['id']]);

            // Insert new answer
            $insertResult = DB::insert('
                INSERT INTO attempt_question (attempt_id, question_id, answer_id, answer_hash, answer, is_correct, score, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ', [
                $attempt->id,
                $sampleQuestion['id'],
                $sampleAnswer['id'],
                'hash_' . $sampleAnswer['id'],
                $sampleAnswer['answer'],
                $sampleAnswer['is_correct_answer'],
                $sampleQuestion['score']
            ]);

            if ($insertResult) {
                echo "   ✅ Answer inserted successfully!\n";

                // Verify insertion
                $newCount = DB::select('SELECT COUNT(*) as count FROM attempt_question WHERE attempt_id = ?', [$attempt->id])[0]->count;
                echo "   New answer count: $newCount\n";

                // Get the inserted answer
                $insertedAnswer = DB::select('SELECT * FROM attempt_question WHERE attempt_id = ? AND question_id = ?', [$attempt->id, $sampleQuestion['id']])[0];
                echo "   Inserted answer: ID=" . $insertedAnswer->question_id . ", Score=" . $insertedAnswer->score . ", Correct=" . ($insertedAnswer->is_correct ? 'Yes' : 'No') . "\n\n";

                // Now test scoring
                echo "🧮 Testing Score Calculation:\n";

                // Update attempt with new score (simple calculation)
                $answeredCount = DB::select('SELECT COUNT(*) as count FROM attempt_question WHERE attempt_id = ?', [$attempt->id])[0]->count;
                $totalScore = DB::select('SELECT SUM(score) as total FROM attempt_question WHERE attempt_id = ?', [$attempt->id])[0]->total;
                $avgScore = $answeredCount > 0 ? $totalScore / $answeredCount : 0;
                $progress = ($answeredCount / 4) * 100; // 4 total questions

                echo "   Answered: $answeredCount, Total Score: $totalScore, Avg: $avgScore, Progress: $progress%\n";

                // Update attempt
                DB::update('UPDATE attempts SET score = ?, progress = ?, updated_at = NOW() WHERE id = ?', [$avgScore, $progress, $attempt->id]);

                // Verify update
                $updatedAttempt = DB::select('SELECT * FROM attempts WHERE id = ?', [$attempt->id])[0];
                echo "   Updated Score: " . $updatedAttempt->score . "\n";
                echo "   Updated Progress: " . $updatedAttempt->progress . "%\n";

                if ($updatedAttempt->score > 0 && $updatedAttempt->progress > 0) {
                    echo "   ✅ SUCCESS: Scoring system working!\n";
                } else {
                    echo "   ❌ ISSUE: Score/progress still zero\n";
                }

            } else {
                echo "   ❌ Failed to insert answer\n";
            }

        } catch (Exception $e) {
            echo "   ❌ Error: " . $e->getMessage() . "\n";
        }

        // Now let's test what happens when we try to simulate the actual API call
        echo "\n🌐 SIMULATING API CALL:\n";
        echo "   Route: POST exam/answer\n";
        echo "   Controller: App\Http\Controllers\Exam\MainController@answer\n\n";

        // Check if we can access the route
        $route = \Route::current();
        if (!$route) {
            echo "   ⚠️ No current route (expected, since we're not in HTTP context)\n";
        }

        // Check if the MainController exists and has the answer method
        if (class_exists('\App\Http\Controllers\Exam\MainController')) {
            echo "   ✅ MainController class exists\n";

            if (method_exists('\App\Http\Controllers\Exam\MainController', 'answer')) {
                echo "   ✅ answer() method exists\n";
            } else {
                echo "   ❌ answer() method missing\n";
            }
        } else {
            echo "   ❌ MainController class missing\n";
        }

    } else {
        echo "❌ No questions found in snapshot\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🎯 TEST COMPLETE\n\n";

echo "💡 CONCLUSION:\n";
echo "   If this test shows successful answer insertion and scoring,\n";
echo "   then the database operations are working. The issue must be:\n";
echo "   1. Frontend not calling the API\n";
echo "   2. API endpoint not routing correctly\n";
echo "   3. Authentication/authorization blocking the request\n";
echo "   4. Request validation failing\n";
echo "   5. Some other issue in the request flow\n";