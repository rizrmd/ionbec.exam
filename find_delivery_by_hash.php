<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "🔍 FINDING DELIVERY BY HASH: 26EAx9r9\n";
echo "======================================\n\n";

try {
    // Cari delivery berdasarkan hash
    $delivery = DB::select('SELECT * FROM deliveries WHERE hash = ?', ['26EAx9r9']);

    if (empty($delivery)) {
        echo "❌ Delivery dengan hash 26EAx9r9 tidak ditemukan\n";

        // Coba cari hash yang mirip
        $similarDeliveries = DB::select("SELECT id, name, hash FROM deliveries WHERE hash LIKE '%26EA%' OR hash LIKE '%x9r9%' LIMIT 5");
        if (!empty($similarDeliveries)) {
            echo "🔍 Delivery dengan hash mirip:\n";
            foreach ($similarDeliveries as $del) {
                echo "   - ID: " . $del->id . ", Name: " . $del->name . ", Hash: " . $del->hash . "\n";
            }
        }
        exit;
    }

    $delivery = $delivery[0];
    echo "✅ Found Delivery:\n";
    echo "   ID: " . $delivery->id . "\n";
    echo "   Name: " . $delivery->name . "\n";
    echo "   Hash: " . $delivery->hash . "\n";
    echo "   Exam ID: " . $delivery->exam_id . "\n";
    echo "   Group ID: " . $delivery->group_id . "\n";
    echo "   Created: " . $delivery->created_at . "\n\n";

    // Periksa apakah delivery ini punya snapshot
    echo "📊 SNAPSHOT CHECK:\n";
    $snapshot = DB::select('SELECT * FROM delivery_snapshots WHERE delivery_id = ?', [$delivery->id]);

    if (!empty($snapshot)) {
        $snapshot = $snapshot[0];
        echo "✅ Snapshot found (ID: " . $snapshot->id . ")\n";
        echo "   Total Questions: " . $snapshot->total_questions . "\n";

        if ($snapshot->exam_structure) {
            echo "✅ Has exam_structure data (" . strlen($snapshot->exam_structure) . " chars)\n";

            // Parse JSON
            $structure = json_decode($snapshot->exam_structure, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "   ✅ JSON is valid\n";

                if (isset($structure['items']) && is_array($structure['items'])) {
                    echo "   Items in structure: " . count($structure['items']) . "\n";

                    if (count($structure['items']) > 0) {
                        $firstItem = $structure['items'][0];
                        echo "   Sample item: " . ($firstItem['title'] ?? 'No Title') . "\n";
                        echo "   Questions in first item: " . (isset($firstItem['questions']) ? count($firstItem['questions']) : 0) . "\n";

                        // Hitung total questions
                        $totalQuestions = 0;
                        foreach ($structure['items'] as $item) {
                            if (isset($item['questions']) && is_array($item['questions'])) {
                                $totalQuestions += count($item['questions']);
                            }
                        }
                        echo "   Total questions in structure: " . $totalQuestions . "\n";
                    }
                }
            }
        }
    } else {
        echo "❌ No snapshot found for delivery\n";
    }

    // Periksa exam items
    echo "\n🎯 EXAM ITEMS CHECK:\n";
    $examItems = DB::select('SELECT COUNT(*) as count FROM exam_item WHERE exam_id = ?', [$delivery->exam_id]);
    echo "   Items linked to Exam " . $delivery->exam_id . ": " . $examItems[0]->count . "\n";

    if ($examItems[0]->count > 0) {
        $itemDetails = DB::select('SELECT ei.item_id, i.title FROM exam_item ei
                                   JOIN items i ON ei.item_id = i.id
                                   WHERE ei.exam_id = ? ORDER BY ei.order LIMIT 3', [$delivery->exam_id]);
        echo "   Sample items:\n";
        foreach ($itemDetails as $item) {
            echo "     - Item " . $item->item_id . ": " . $item->title . "\n";
        }

        // Hitung questions untuk items ini
        $itemIds = array_map(fn($item) => $item->item_id, $itemDetails);
        if (!empty($itemIds)) {
            $itemIdsStr = implode(',', $itemIds);
            $questionCount = DB::select("SELECT COUNT(q.id) as count FROM questions q WHERE q.item_id IN ($itemIdsStr)");
            echo "   Questions for sample items: " . $questionCount[0]->count . "\n";
        }
    }

    // Periksa attempts
    echo "\n🎯 ATTEMPTS CHECK:\n";
    $attempts = DB::select('SELECT id, score, progress, created_at FROM attempts WHERE delivery_id = ? ORDER BY created_at DESC LIMIT 3', [$delivery->id]);
    echo "   Attempts found: " . count($attempts) . "\n";

    foreach ($attempts as $attempt) {
        echo "   - Attempt " . $attempt->id . ": Score=" . $attempt->score . ", Progress=" . $attempt->progress . "%\n";
    }

    // Periksa answer records
    if (!empty($attempts)) {
        $attemptIds = array_map(fn($a) => $a->id, $attempts);
        $attemptIdsStr = implode(',', $attemptIds);
        $answerRecords = DB::select("SELECT COUNT(*) as count FROM attempt_questions WHERE attempt_id IN ($attemptIdsStr)");
        echo "   Answer records found: " . $answerRecords[0]->count . "\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🎯 ANALYSIS COMPLETE\n";