<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "🔍 CHECKING MISSING QUESTION HASH: VxgOjk3W\n";
echo "========================================\n\n";

try {
    // Get delivery snapshot
    $snapshot = DB::select('SELECT * FROM delivery_snapshots WHERE delivery_id = 152')[0];
    $structure = json_decode($snapshot->exam_structure, true);

    if ($structure && isset($structure['items'])) {
        echo "📋 ALL QUESTIONS IN SNAPSHOT:\n";
        $allQuestions = [];

        foreach ($structure['items'] as $itemIndex => $item) {
            if (isset($item['questions'])) {
                foreach ($item['questions'] as $question) {
                    $allQuestions[] = [
                        'hash' => $question['hash'] ?? 'N/A',
                        'id' => $question['id'] ?? 'N/A',
                        'title' => substr($question['question'] ?? 'N/A', 0, 50) . '...'
                    ];
                }
            }
        }

        echo "   Total questions: " . count($allQuestions) . "\n\n";

        // Check if VxgOjk3W exists
        $foundVxgOjk3W = false;
        $foundXzVQeVvO = false;

        echo "🔍 CHECKING SPECIFIC HASHES:\n";
        foreach ($allQuestions as $question) {
            if ($question['hash'] === 'VxgOjk3W') {
                echo "   ✅ Found VxgOjk3W: ID " . $question['id'] . " - " . $question['title'] . "\n";
                $foundVxgOjk3W = true;
            }
            if ($question['hash'] === 'XzVQeVvO') {
                echo "   ✅ Found XzVQeVvO: ID " . $question['id'] . " - " . $question['title'] . "\n";
                $foundXzVQeVvO = true;
            }
        }

        if (!$foundVxgOjk3W) {
            echo "   ❌ VxgOjk3W NOT FOUND in snapshot!\n";
        }
        if (!$foundXzVQeVvO) {
            echo "   ❌ XzVQeVvO NOT FOUND in snapshot!\n";
        }

        echo "\n📋 ALL QUESTION HASHES:\n";
        foreach ($allQuestions as $index => $question) {
            echo sprintf("   %2d. %s\n", $index + 1, $question['hash']);
        }

    } else {
        echo "❌ No items found in snapshot structure\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🎯 ANALYSIS COMPLETE\n";