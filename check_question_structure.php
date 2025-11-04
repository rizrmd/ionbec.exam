<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "🔍 CHECKING QUESTION STRUCTURE IN SNAPSHOT\n";
echo "======================================\n\n";

try {
    // Get delivery snapshot
    $snapshot = DB::select('SELECT * FROM delivery_snapshots WHERE delivery_id = 152')[0];
    $structure = json_decode($snapshot->exam_structure, true);

    if ($structure && isset($structure['items'])) {
        echo "📋 SNAPSHOT STRUCTURE ANALYSIS:\n";
        echo "   Total items: " . count($structure['items']) . "\n\n";

        foreach ($structure['items'] as $itemIndex => $item) {
            echo "📄 Item " . ($itemIndex + 1) . ":\n";
            echo "   Title: " . ($item['title'] ?? 'N/A') . "\n";

            if (isset($item['questions'])) {
                echo "   Questions: " . count($item['questions']) . "\n";

                foreach ($item['questions'] as $qIndex => $question) {
                    echo "     Question " . ($qIndex + 1) . ":\n";
                    echo "       Keys: " . implode(', ', array_keys($question)) . "\n";

                    // Check for different possible hash field names
                    $hash = $question['hash'] ?? $question['question_hash'] ?? $question['id_hash'] ?? 'N/A';
                    $id = $question['id'] ?? 'N/A';

                    echo "       Hash: " . $hash . "\n";
                    echo "       ID: " . $id . "\n";

                    if (isset($question['answers'])) {
                        echo "       Answers: " . count($question['answers']) . "\n";

                        foreach ($question['answers'] as $aIndex => $answer) {
                            $answerHash = $answer['hash'] ?? $answer['answer_hash'] ?? $answer['id_hash'] ?? 'N/A';
                            echo "         Answer " . ($aIndex + 1) . " Hash: " . $answerHash . "\n";
                        }
                    }
                    echo "\n";
                }
            } else {
                echo "   No questions found\n";
            }
            echo "\n";
        }

    } else {
        echo "❌ No items found in snapshot structure\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🎯 ANALYSIS COMPLETE\n";