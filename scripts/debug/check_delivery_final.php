<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "🔍 FINAL DELIVERY 71 INVESTIGATION\n";
echo "==================================\n\n";

try {
    // First, check delivery table structure
    echo "📋 DELIVERY TABLE STRUCTURE:\n";
    $columns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'deliveries' ORDER BY ordinal_position");
    echo "   Columns: ";
    $columnNames = [];
    foreach ($columns as $col) {
        $columnNames[] = $col->column_name;
    }
    echo implode(', ', $columnNames) . "\n\n";

    // Get delivery info
    echo "📦 DELIVERY INFO:\n";
    $delivery = DB::select('SELECT * FROM deliveries WHERE id = 71');

    if (empty($delivery)) {
        echo "❌ Delivery ID 71 not found\n";
        exit;
    }

    $delivery = $delivery[0];
    echo "✅ Found Delivery ID: " . $delivery->id . "\n";
    echo "   Name: " . $delivery->name . "\n";
    echo "   Exam ID: " . $delivery->exam_id . "\n";
    echo "   Group ID: " . $delivery->group_id . "\n";
    echo "   Created: " . $delivery->created_at . "\n\n";

    // Check if snapshot exists
    echo "📊 SNAPSHOT CHECK:\n";
    $snapshot = DB::select('SELECT * FROM delivery_snapshots WHERE delivery_id = 71');

    if (!empty($snapshot)) {
        $snapshot = $snapshot[0];
        echo "✅ Snapshot found (ID: " . $snapshot->id . ")\n";
        echo "   Total Questions: " . $snapshot->total_questions . "\n";
        echo "   Created: " . $snapshot->created_at . "\n";

        if ($snapshot->exam_structure) {
            echo "✅ Has exam_structure data (" . strlen($snapshot->exam_structure) . " chars)\n";

            // Try to parse JSON
            $structure = json_decode($snapshot->exam_structure, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "   ✅ JSON is valid\n";

                if (isset($structure['items']) && is_array($structure['items'])) {
                    echo "   Items in structure: " . count($structure['items']) . "\n";

                    if (count($structure['items']) > 0) {
                        $firstItem = $structure['items'][0];
                        echo "   Sample item: " . ($firstItem['title'] ?? 'No Title') . "\n";
                        echo "   Questions in first item: " . (isset($firstItem['questions']) ? count($firstItem['questions']) : 0) . "\n";

                        // Count total questions in structure
                        $totalQuestions = 0;
                        foreach ($structure['items'] as $item) {
                            if (isset($item['questions']) && is_array($item['questions'])) {
                                $totalQuestions += count($item['questions']);
                            }
                        }
                        echo "   Total questions in structure: " . $totalQuestions . "\n";
                    } else {
                        echo "   ❌ No items in structure\n";
                    }
                } else {
                    echo "   ❌ No 'items' array in structure\n";
                }
            } else {
                echo "   ❌ JSON parsing error: " . json_last_error_msg() . "\n";
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
            // Get sample item with questions
            $sampleItem = DB::select('SELECT i.id, i.title, COUNT(q.id) as question_count
                                     FROM items i
                                     LEFT JOIN questions q ON i.id = q.item_id
                                     WHERE i.exam_id = ?
                                     GROUP BY i.id, i.title
                                     LIMIT 1', [$exam->id]);
            if (!empty($sampleItem)) {
                $item = $sampleItem[0];
                echo "   Sample item: " . $item->title . " (ID: " . $item->id . ")\n";
                echo "   Questions in sample item: " . $item->question_count . "\n";
            }
        }
    } else {
        echo "❌ Exam not found for ID: " . $delivery->exam_id . "\n";
    }

    echo "\n🎯 ATTEMPTS & ANSWERS:\n";
    // Check attempts for this delivery
    $attempts = DB::select('SELECT id, score, progress, created_at FROM attempts WHERE delivery_id = 71');
    echo "   Attempts found: " . count($attempts) . "\n";

    foreach ($attempts as $attempt) {
        echo "   - Attempt " . $attempt->id . ": Score=" . $attempt->score . ", Progress=" . $attempt->progress . "%, Created=" . $attempt->created_at . "\n";
    }

    // Check attempt_questions records
    if (!empty($attempts)) {
        $attemptIds = array_map(fn($a) => $a->id, $attempts);
        $attemptIdsStr = implode(',', $attemptIds);
        $answerRecords = DB::select("SELECT COUNT(*) as count FROM attempt_questions WHERE attempt_id IN ($attemptIdsStr)");
        echo "   Answer records found: " . $answerRecords[0]->count . "\n";

        if ($answerRecords[0]->count > 0) {
            $sampleAnswers = DB::select("SELECT attempt_id, question_id, score, is_correct FROM attempt_questions WHERE attempt_id IN ($attemptIdsStr) LIMIT 3");
            foreach ($sampleAnswers as $answer) {
                echo "     - Attempt " . $answer->attempt_id . ", Question " . $answer->question_id . ": Score=" . $answer->score . ", Correct=" . ($answer->is_correct ? 'Yes' : 'No') . "\n";
            }
        }
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🎯 FINAL CONCLUSION:\n";
echo "==================\n";
echo "This analysis shows why answers are not being saved and scores remain zero.\n";