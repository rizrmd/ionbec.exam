<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "🔍 DIRECT DATABASE CHECK FOR DELIVERY 71\n";
echo "========================================\n\n";

// Direct database queries to avoid model issues
try {
    // Get delivery info directly from database
    $delivery = DB::select('SELECT id, name, exam_id, status FROM deliveries WHERE id = 71');

    if (empty($delivery)) {
        echo "❌ Delivery ID 71 not found\n";
        exit;
    }

    $delivery = $delivery[0];
    echo "✅ Found Delivery: " . $delivery->name . " (ID: " . $delivery->id . ")\n";
    echo "   Exam ID: " . $delivery->exam_id . "\n";
    echo "   Status: " . $delivery->status . "\n\n";

    // Check if snapshot exists
    echo "📊 SNAPSHOT CHECK:\n";
    $snapshot = DB::select('SELECT id, total_questions, exam_structure FROM delivery_snapshots WHERE delivery_id = 71');

    if (!empty($snapshot)) {
        $snapshot = $snapshot[0];
        echo "✅ Snapshot found (ID: " . $snapshot->id . ")\n";
        echo "   Total Questions: " . $snapshot->total_questions . "\n";

        if ($snapshot->exam_structure) {
            echo "✅ Has exam_structure data\n";

            // Try to parse JSON
            $structure = json_decode($snapshot->exam_structure, true);
            if ($structure && isset($structure['items'])) {
                echo "   Items in structure: " . count($structure['items']) . "\n";

                if (count($structure['items']) > 0) {
                    $firstItem = $structure['items'][0];
                    echo "   Sample item: " . ($firstItem['title'] ?? 'No Title') . "\n";
                    echo "   Questions in first item: " . (isset($firstItem['questions']) ? count($firstItem['questions']) : 0) . "\n";
                }
            } else {
                echo "   ❌ Could not parse exam_structure JSON or no items found\n";
            }
        } else {
            echo "   ❌ No exam_structure data\n";
        }
    } else {
        echo "❌ No snapshot found for delivery\n";
    }

    echo "\n🎯 EXAM ITEMS CHECK:\n";
    // Check exam items directly
    $exam = DB::select('SELECT id, name FROM exams WHERE id = ?', [$delivery->exam_id]);

    if (!empty($exam)) {
        $exam = $exam[0];
        echo "✅ Found Exam: " . $exam->name . " (ID: " . $exam->id . ")\n";

        // Count items for this exam
        $itemCount = DB::select('SELECT COUNT(*) as count FROM items WHERE exam_id = ?', [$exam->id]);
        echo "   Items in exam: " . $itemCount[0]->count . "\n";

        // Count questions
        $questionCount = DB::select('SELECT COUNT(q.id) as count FROM questions q
                                     INNER JOIN items i ON q.item_id = i.id
                                     WHERE i.exam_id = ?', [$exam->id]);
        echo "   Total questions: " . $questionCount[0]->count . "\n";

        if ($itemCount[0]->count > 0) {
            // Get sample item
            $sampleItem = DB::select('SELECT id, title FROM items WHERE exam_id = ? LIMIT 1', [$exam->id]);
            if (!empty($sampleItem)) {
                $item = $sampleItem[0];
                echo "   Sample item: " . $item->title . " (ID: " . $item->id . ")\n";

                // Count questions for this item
                $itemQuestions = DB::select('SELECT COUNT(*) as count FROM questions WHERE item_id = ?', [$item->id]);
                echo "   Questions in sample item: " . $itemQuestions[0]->count . "\n";
            }
        }
    } else {
        echo "❌ Exam not found for ID: " . $delivery->exam_id . "\n";
    }

    echo "\n🎯 ATTEMPTS CHECK:\n";
    // Check attempts for this delivery
    $attempts = DB::select('SELECT id, score, progress FROM attempts WHERE delivery_id = 71');
    echo "   Attempts found: " . count($attempts) . "\n";

    foreach ($attempts as $attempt) {
        echo "   - Attempt " . $attempt->id . ": Score=" . $attempt->score . ", Progress=" . $attempt->progress . "%\n";
    }

    echo "\n🎯 ANSWER RECORDS CHECK:\n";
    // Check attempt_questions records
    $answerRecords = DB::select('SELECT COUNT(*) as count FROM attempt_questions WHERE attempt_id IN (SELECT id FROM attempts WHERE delivery_id = 71)');
    echo "   Answer records found: " . $answerRecords[0]->count . "\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🎯 ANALYSIS COMPLETE\n";