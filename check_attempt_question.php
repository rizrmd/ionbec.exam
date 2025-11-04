<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "🔍 CHECKING ATTEMPT_QUESTION TABLE FOR DELIVERY 152\n";
echo "================================================\n\n";

try {
    // Periksa structure attempt_question
    echo "📋 ATTEMPT_QUESTION TABLE STRUCTURE:\n";
    $columns = DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'attempt_question' ORDER BY ordinal_position");
    foreach ($columns as $col) {
        echo "   - " . $col->column_name . " (" . $col->data_type . ")\n";
    }
    echo "\n";

    // Periksa apakah ada records untuk attempt ID 2
    echo "🎯 ANSWER RECORDS FOR ATTEMPT ID 2:\n";
    $answerRecords = DB::select('SELECT COUNT(*) as count FROM attempt_question WHERE attempt_id = 2');
    echo "   Total answer records: " . $answerRecords[0]->count . "\n\n";

    if ($answerRecords[0]->count > 0) {
        echo "📄 SAMPLE ANSWER RECORDS:\n";
        $sampleAnswers = DB::select('SELECT * FROM attempt_question WHERE attempt_id = 2 LIMIT 5');
        foreach ($sampleAnswers as $answer) {
            echo "   - Question ID: " . $answer->question_id . "\n";
            echo "     Answer ID: " . ($answer->answer_id ?? 'N/A') . "\n";
            echo "     Score: " . $answer->score . "\n";
            echo "     Is Correct: " . ($answer->is_correct ? 'Yes' : 'No') . "\n";
            echo "     Answer Text: " . substr($answer->answer ?? 'N/A', 0, 50) . "...\n";
            echo "     Created: " . $answer->created_at . "\n\n";
        }
    } else {
        echo "❌ NO ANSWER RECORDS FOUND\n";
        echo "   This explains why score and progress are at 0!\n\n";
    }

    // Periksa semua attempt_question records di database
    echo "📊 ALL ATTEMPT_QUESTION RECORDS IN DATABASE:\n";
    $allRecords = DB::select('SELECT COUNT(*) as count FROM attempt_question');
    echo "   Total records in attempt_question: " . $allRecords[0]->count . "\n";

    if ($allRecords[0]->count > 0) {
        $recentRecords = DB::select('SELECT attempt_id, question_id, score, is_correct, created_at FROM attempt_question ORDER BY created_at DESC LIMIT 5');
        echo "   Recent records:\n";
        foreach ($recentRecords as $record) {
            echo "     - Attempt " . $record->attempt_id . ", Question " . $record->question_id . ": Score=" . $record->score . ", Correct=" . ($record->is_correct ? 'Yes' : 'No') . ", Date=" . $record->created_at . "\n";
        }
    }

    // Test simulate jawaban untuk delivery 152
    echo "\n🧪 TESTING ANSWER SUBMISSION FOR DELIVERY 152:\n";
    $delivery = DB::select('SELECT * FROM deliveries WHERE id = 152')[0];
    $attempt = DB::select('SELECT * FROM attempts WHERE delivery_id = 152 ORDER BY created_at DESC LIMIT 1')[0];

    echo "   Delivery: " . $delivery->name . " (ID: " . $delivery->id . ")\n";
    echo "   Attempt: ID " . $attempt->id . ", Hash: " . $attempt->hash . "\n";
    echo "   Current Score: " . $attempt->score . ", Progress: " . $attempt->progress . "%\n\n";

    // Coba ambil satu pertanyaan dari snapshot untuk testing
    $snapshot = DB::select('SELECT * FROM delivery_snapshots WHERE delivery_id = 152')[0];
    $structure = json_decode($snapshot->exam_structure, true);

    if ($structure && isset($structure['items'][0]['questions'][0])) {
        $sampleQuestion = $structure['items'][0]['questions'][0];
        echo "   Sample Question from snapshot:\n";
        echo "   - Question ID: " . ($sampleQuestion['id'] ?? 'N/A') . "\n";
        echo "   - Question: " . substr($sampleQuestion['question'] ?? 'N/A', 0, 50) . "...\n";
        echo "   - Answers: " . (isset($sampleQuestion['answers']) ? count($sampleQuestion['answers']) : 0) . "\n";

        if (isset($sampleQuestion['answers'][0])) {
            $sampleAnswer = $sampleQuestion['answers'][0];
            echo "   - Sample Answer ID: " . ($sampleAnswer['id'] ?? 'N/A') . "\n";
            echo "   - Sample Answer: " . substr($sampleAnswer['answer'] ?? 'N/A', 0, 30) . "...\n";
            echo "   - Is Correct: " . (($sampleAnswer['is_correct_answer'] ?? false) ? 'Yes' : 'No') . "\n";
        }
    }

    echo "\n🎯 ROOT CAUSE ANALYSIS:\n";
    if ($answerRecords[0]->count == 0) {
        echo "   ❌ CONFIRMED: No answer records found for Attempt ID 2\n";
        echo "   ✅ Delivery 152 has proper snapshot with 4 questions\n";
        echo "   ✅ Attempt 2 exists but has no saved answers\n";
        echo "   ❌ This indicates the answer submission process is failing\n\n";

        echo "🔧 NEXT STEPS TO FIX:\n";
        echo "   1. Test the actual answer submission API endpoint\n";
        echo "   2. Check if MainController::answer() is being called\n";
        echo "   3. Verify the request/response flow\n";
        echo "   4. Debug why answers aren't being saved to attempt_question table\n";
    } else {
        echo "   ✅ Answer records exist - need to investigate scoring calculation\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🎯 INVESTIGATION COMPLETE\n";