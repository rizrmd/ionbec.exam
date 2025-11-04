<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "🧪 TESTING ACTUAL API CALL (Frontend Simulation)\n";
echo "==============================================\n\n";

try {
    // Get the exact data that frontend would send
    $attempt = DB::select('SELECT * FROM attempts WHERE delivery_id = 152 ORDER BY created_at DESC LIMIT 1')[0];
    $snapshot = DB::select('SELECT * FROM delivery_snapshots WHERE delivery_id = 152')[0];
    $structure = json_decode($snapshot->exam_structure, true);

    echo "📋 API Call Simulation:\n";
    echo "   Attempt Hash: " . $attempt->hash . "\n";
    echo "   Attempt ID: " . $attempt->id . "\n\n";

    if ($structure && isset($structure['items'][0]['questions'][0])) {
        $sampleQuestion = $structure['items'][0]['questions'][0];
        $sampleAnswer = $sampleQuestion['answers'][0];

        echo "📝 Test Data (exactly like frontend):\n";
        echo "   Question Hash: " . $sampleQuestion['hash'] . "\n";
        echo "   Answer Hash: " . $sampleAnswer['hash'] . "\n";
        echo "   Question ID: " . $sampleQuestion['id'] . "\n";
        echo "   Answer ID: " . $sampleAnswer['id'] . "\n\n";

        // Simulate exact frontend request format
        $requestData = [
            'attempt_hash' => $attempt->hash,
            'answers_value' => [
                $sampleQuestion['hash'] => $sampleAnswer['hash']
            ]
        ];

        echo "📡 Request Data (exact frontend format):\n";
        echo json_encode($requestData, JSON_PRETTY_PRINT) . "\n\n";

        // Create a mock HTTP request to simulate the API call
        echo "🔄 Simulating POST /exam/answer...\n";

        try {
            // Get the MainController
            $mainController = new \App\Http\Controllers\Exam\MainController();

            // Create a mock request
            $mockRequest = new \Illuminate\Http\Request();
            $mockRequest->merge($requestData);

            echo "   ✅ Mock request created\n";
            echo "   📋 Calling MainController::answer() method...\n\n";

            // Mock session/auth if needed
            if (method_exists($mainController, 'answer')) {
                // Create the route parameters that would be passed by Laravel
                $routeParameters = [];

                echo "🎯 EXECUTING MainController::answer():\n";

                // Call the actual method
                $response = $mainController->answer($mockRequest);

                echo "   ✅ Method executed successfully!\n";
                echo "   📋 Response type: " . get_class($response) . "\n";

                if (method_exists($response, 'getData')) {
                    $responseData = $response->getData();
                    echo "   📋 Response data: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
                } else {
                    echo "   📋 Response content: " . $response . "\n";
                }

            } else {
                echo "   ❌ answer() method not found\n";
            }

        } catch (Exception $e) {
            echo "   ❌ Error calling MainController::answer(): " . $e->getMessage() . "\n";
            echo "   📋 Stack trace: " . $e->getTraceAsString() . "\n";
        }

        // Check if answer was actually saved
        echo "\n🔍 VERIFYING RESULT:\n";
        $answerCount = DB::select('SELECT COUNT(*) as count FROM attempt_question WHERE attempt_id = ?', [$attempt->id])[0]->count;
        echo "   Answer records after API call: $answerCount\n";

        if ($answerCount > 0) {
            $latestAnswer = DB::select('SELECT * FROM attempt_question WHERE attempt_id = ? ORDER BY created_at DESC LIMIT 1', [$attempt->id])[0];
            echo "   Latest answer:\n";
            echo "     - Question ID: " . $latestAnswer->question_id . "\n";
            echo "     - Answer ID: " . $latestAnswer->answer_id . "\n";
            echo "     - Score: " . $latestAnswer->score . "\n";
            echo "     - Is Correct: " . ($latestAnswer->is_correct ? 'Yes' : 'No') . "\n";

            // Check updated attempt
            $updatedAttempt = DB::select('SELECT * FROM attempts WHERE id = ?', [$attempt->id])[0];
            echo "   Updated attempt score: " . $updatedAttempt->score . "\n";
            echo "   Updated attempt progress: " . $updatedAttempt->progress . "%\n";
        }

    } else {
        echo "❌ No questions found in snapshot\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🎯 API TEST COMPLETE\n\n";

echo "💡 CONCLUSION:\n";
echo "   If this test shows the API call working, then:\n";
echo "   1. Backend is 100% functional\n";
echo "   2. Issue must be in frontend execution\n";
echo "   3. Possible frontend issues:\n";
echo "      - JavaScript errors preventing API call\n";
echo "      - Network request being blocked\n";
echo "      - CSRF token issues\n";
echo "      - Authentication/session issues\n";