<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "🔍 DATABASE STRUCTURE ANALYSIS\n";
echo "===============================\n\n";

try {
    // Check items table structure
    echo "📋 ITEMS TABLE STRUCTURE:\n";
    $itemColumns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'items' ORDER BY ordinal_position");
    echo "   Columns: ";
    $itemColumnNames = [];
    foreach ($itemColumns as $col) {
        $itemColumnNames[] = $col->column_name;
    }
    echo implode(', ', $itemColumnNames) . "\n\n";

    // Check if there's a test_id or similar column instead of exam_id
    echo "🔍 ITEMS SAMPLE DATA:\n";
    $sampleItems = DB::select('SELECT * FROM items LIMIT 3');
    if (!empty($sampleItems)) {
        foreach ($sampleItems as $item) {
            echo "   Item ID: " . $item->id . ", Title: " . ($item->title ?? 'N/A') . "\n";
            if (isset($item->test_id)) echo "     Test ID: " . $item->test_id . "\n";
            if (isset($item->exam_id)) echo "     Exam ID: " . $item->exam_id . "\n";
            echo "\n";
        }
    } else {
        echo "   No items found in database\n\n";
    }

    // Check if there's a tests table instead of exams
    echo "📋 TESTS TABLE STRUCTURE:\n";
    $testsTableExists = DB::select("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'tests')");
    if ($testsTableExists[0]->exists) {
        $testColumns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'tests' ORDER BY ordinal_position");
        echo "   Columns: ";
        $testColumnNames = [];
        foreach ($testColumns as $col) {
            $testColumnNames[] = $col->column_name;
        }
        echo implode(', ', $testColumnNames) . "\n\n";

        // Check if exam_id 8 should be test_id 8
        echo "🔍 CHECKING TEST ID 8:\n";
        $test8 = DB::select('SELECT * FROM tests WHERE id = 8');
        if (!empty($test8)) {
            $test = $test8[0];
            echo "   ✅ Found Test ID 8: " . ($test->name ?? $test->title ?? 'N/A') . "\n";

            // Count items for test 8
            $itemsForTest8 = DB::select('SELECT COUNT(*) as count FROM items WHERE test_id = 8');
            echo "   Items for Test 8: " . $itemsForTest8[0]->count . "\n";

            // Check questions for these items
            if ($itemsForTest8[0]->count > 0) {
                $questionsForTest8 = DB::select('SELECT COUNT(q.id) as count FROM questions q
                                                INNER JOIN items i ON q.item_id = i.id
                                                WHERE i.test_id = 8');
                echo "   Questions for Test 8: " . $questionsForTest8[0]->count . "\n";
            }
        } else {
            echo "   ❌ Test ID 8 not found\n";
        }
    } else {
        echo "   No 'tests' table found\n\n";
    }

    // Check if there are any snapshots at all
    $allSnapshots = DB::select('SELECT COUNT(*) as count FROM delivery_snapshots');
    echo "📊 TOTAL SNAPSHOTS: " . $allSnapshots[0]->count . "\n";

    if ($allSnapshots[0]->count > 0) {
        $recentSnapshots = DB::select('SELECT delivery_id, total_questions, created_at FROM delivery_snapshots ORDER BY created_at DESC LIMIT 3');
        echo "   Recent snapshots:\n";
        foreach ($recentSnapshots as $snapshot) {
            echo "     - Delivery " . $snapshot->delivery_id . ": " . $snapshot->total_questions . " questions, " . $snapshot->created_at . "\n";
        }
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🎯 ANALYSIS COMPLETE\n";