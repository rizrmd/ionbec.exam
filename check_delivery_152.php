<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "🔍 INVESTIGATING DELIVERY 152 (Hash: 26EAx9r9)\n";
echo "=============================================\n\n";

try {
    // Cek semua tabel yang ada untuk menyimpan answers
    echo "📋 LOOKING FOR ANSWER TABLES:\n";
    $allTables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");

    $answerTables = [];
    foreach ($allTables as $table) {
        $tableName = $table->table_name;
        if (strpos($tableName, 'answer') !== false || strpos($tableName, 'attempt') !== false) {
            $answerTables[] = $tableName;
        }
    }
    echo "   Tables with answer/attempt in name: " . implode(', ', $answerTables) . "\n\n";

    // Periksa attempt_questions atau attempt_answers
    $correctTable = null;
    foreach ($answerTables as $table) {
        if ($table === 'attempt_questions' || $table === 'attempt_answers') {
            $correctTable = $table;
            break;
        }
    }

    if ($correctTable) {
        echo "✅ Found answer table: $correctTable\n";

        // Periksa structure
        $columns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = '$correctTable' ORDER BY ordinal_position");
        echo "   Columns: ";
        $columnNames = [];
        foreach ($columns as $col) {
            $columnNames[] = $col->column_name;
        }
        echo implode(', ', $columnNames) . "\n\n";
    } else {
        echo "❌ No answer table found, checking attempt tables...\n";
    }

    // Periksa delivery 152 details
    echo "📦 DELIVERY 152 DETAILS:\n";
    $delivery = DB::select('SELECT * FROM deliveries WHERE id = 152')[0];
    echo "   ID: " . $delivery->id . "\n";
    echo "   Name: " . $delivery->name . "\n";
    echo "   Exam ID: " . $delivery->exam_id . "\n";
    echo "   Created: " . $delivery->created_at . "\n\n";

    // Periksa attempts untuk delivery 152
    echo "🎯 ATTEMPTS FOR DELIVERY 152:\n";
    $attempts = DB::select('SELECT * FROM attempts WHERE delivery_id = 152 ORDER BY created_at DESC');

    foreach ($attempts as $attempt) {
        echo "   Attempt ID: " . $attempt->id . "\n";
        echo "   Score: " . $attempt->score . "\n";
        echo "   Progress: " . $attempt->progress . "%\n";
        echo "   Created: " . $attempt->created_at . "\n";
        echo "   Hash: " . $attempt->hash . "\n\n";

        // Cari answer records untuk attempt ini
        if ($correctTable) {
            $answerRecords = DB::select("SELECT COUNT(*) as count FROM $correctTable WHERE attempt_id = ?", [$attempt->id]);
            echo "   Answer records in $correctTable: " . $answerRecords[0]->count . "\n";

            if ($answerRecords[0]->count > 0) {
                $sampleAnswers = DB::select("SELECT * FROM $correctTable WHERE attempt_id = ? LIMIT 3", [$attempt->id]);
                echo "   Sample answer records:\n";
                foreach ($sampleAnswers as $answer) {
                    echo "     - Question: " . ($answer->question_id ?? 'N/A') . ", Score: " . ($answer->score ?? 'N/A') . ", Correct: " . (($answer->is_correct ?? false) ? 'Yes' : 'No') . "\n";
                }
            }
        }
        echo "\n";
    }

    // Periksa snapshot structure
    echo "📊 SNAPSHOT ANALYSIS:\n";
    $snapshot = DB::select('SELECT * FROM delivery_snapshots WHERE delivery_id = 152')[0];
    echo "   Snapshot ID: " . $snapshot->id . "\n";
    echo "   Total Questions: " . $snapshot->total_questions . "\n";

    $structure = json_decode($snapshot->exam_structure, true);
    if ($structure && isset($structure['items'])) {
        echo "   Items in snapshot: " . count($structure['items']) . "\n";

        $totalQuestions = 0;
        foreach ($structure['items'] as $item) {
            if (isset($item['questions']) && is_array($item['questions'])) {
                $totalQuestions += count($item['questions']);
            }
        }
        echo "   Total questions calculated: " . $totalQuestions . "\n";

        // Tampilkan detail beberapa pertanyaan
        echo "   Sample questions:\n";
        $questionCount = 0;
        foreach ($structure['items'] as $item) {
            if (isset($item['questions']) && $questionCount < 3) {
                foreach ($item['questions'] as $question) {
                    if ($questionCount >= 3) break;
                    echo "     - Q" . ($questionCount + 1) . ": " . substr($question['question'] ?? 'No question text', 0, 50) . "...\n";
                    echo "       Answers: " . (isset($question['answers']) ? count($question['answers']) : 0) . "\n";
                    $questionCount++;
                }
            }
        }
    }

    // Test API endpoint yang mungkin digunakan
    echo "\n🔧 TESTING API STRUCTURE:\n";
    echo "   Based on URL pattern, the API endpoint should be:\n";
    echo "   GET/POST /api/exams/{delivery_hash}/questions\n";
    echo "   POST /api/exams/{delivery_hash}/answers\n\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🎯 DELIVERY 152 ANALYSIS COMPLETE\n";