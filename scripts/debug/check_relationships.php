<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "🔍 DELIVERY-ITEMS RELATIONSHIP ANALYSIS\n";
echo "======================================\n\n";

try {
    // Check for exam_items table or similar linking table
    echo "📋 LOOKING FOR LINKING TABLES:\n";
    $tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name LIKE '%exam%' OR table_name LIKE '%item%' ORDER BY table_name");

    $relevantTables = [];
    foreach ($tables as $table) {
        $relevantTables[] = $table->table_name;
    }
    echo "   Tables with exam/item in name: " . implode(', ', $relevantTables) . "\n\n";

    // Check if there's an exam_items table
    if (in_array('exam_items', $relevantTables)) {
        echo "🔗 EXAM_ITEMS TABLE FOUND:\n";
        $examItemsColumns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'exam_items' ORDER BY ordinal_position");
        echo "   Columns: ";
        $columnNames = [];
        foreach ($examItemsColumns as $col) {
            $columnNames[] = $col->column_name;
        }
        echo implode(', ', $columnNames) . "\n";

        // Check if there are records for exam_id 8
        $examItemsFor8 = DB::select('SELECT COUNT(*) as count FROM exam_items WHERE exam_id = 8');
        echo "   Records for Exam ID 8: " . $examItemsFor8[0]->count . "\n";

        if ($examItemsFor8[0]->count > 0) {
            $sampleExamItems = DB::select('SELECT * FROM exam_items WHERE exam_id = 8 LIMIT 3');
            foreach ($sampleExamItems as $record) {
                echo "     - Exam: " . $record->exam_id . ", Item: " . $record->item_id . "\n";
            }
        }
        echo "\n";
    }

    // Check delivery_exams or similar
    if (in_array('delivery_exams', $relevantTables)) {
        echo "🔗 DELIVERY_EXAMS TABLE FOUND:\n";
        $deliveryExamsColumns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'delivery_exams' ORDER BY ordinal_position");
        echo "   Columns: ";
        $columnNames = [];
        foreach ($deliveryExamsColumns as $col) {
            $columnNames[] = $col->column_name;
        }
        echo implode(', ', $columnNames) . "\n\n";
    }

    // Check if exam_items table exists with different name
    echo "🔍 CHECKING FOR EXAM-ITEM LINKS:\n";
    $allTables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");

    $possibleLinkTables = [];
    foreach ($allTables as $table) {
        $tableName = $table->table_name;
        if ((strpos($tableName, 'exam') !== false && strpos($tableName, 'item') !== false) ||
            (strpos($tableName, 'test') !== false && strpos($tableName, 'item') !== false)) {
            $possibleLinkTables[] = $tableName;
        }
    }

    if (!empty($possibleLinkTables)) {
        echo "   Possible linking tables: " . implode(', ', $possibleLinkTables) . "\n";

        // Check the first one found
        $linkTable = $possibleLinkTables[0];
        echo "\n🔗 ANALYZING $linkTable:\n";
        $linkColumns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = '$linkTable' ORDER BY ordinal_position");
        echo "   Columns: ";
        $columnNames = [];
        foreach ($linkColumns as $col) {
            $columnNames[] = $col->column_name;
        }
        echo implode(', ', $columnNames) . "\n";

        // Check for exam_id 8 in this table
        if (in_array('exam_id', $columnNames)) {
            $recordsFor8 = DB::select("SELECT COUNT(*) as count FROM $linkTable WHERE exam_id = 8");
            echo "   Records for Exam ID 8: " . $recordsFor8[0]->count . "\n";
        }
    } else {
        echo "   No exam-item linking tables found\n";
    }

    echo "\n🎯 CHECKING EXAM 8 ITEM RELATIONSHIPS:\n";
    // Let's check if there's a model relationship we're missing
    // Check items table again for any test_id or exam_id field that might have been missed
    $itemColumnsDetail = DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'items' ORDER BY ordinal_position");
    echo "   Items table detailed columns:\n";
    foreach ($itemColumnsDetail as $col) {
        echo "     - " . $col->column_name . " (" . $col->data_type . ")\n";
    }

    // Check if exam_items table actually exists
    $examItemsExists = DB::select("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'exam_items')");
    if ($examItemsExists[0]->exists) {
        echo "\n✅ exam_items table exists - this is the missing link!\n";

        // Get item IDs for exam 8
        $itemsForExam8 = DB::select('SELECT item_id FROM exam_items WHERE exam_id = 8');
        echo "   Item IDs for Exam 8: ";
        $itemIds = [];
        foreach ($itemsForExam8 as $record) {
            $itemIds[] = $record->item_id;
        }
        echo implode(', ', $itemIds) . "\n";

        // Count questions for these items
        if (!empty($itemIds)) {
            $itemIdsStr = implode(',', $itemIds);
            $questionsForItems = DB::select("SELECT COUNT(q.id) as count FROM questions q WHERE q.item_id IN ($itemIdsStr)");
            echo "   Total questions for these items: " . $questionsForItems[0]->count . "\n";
        }
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🎯 RELATIONSHIP ANALYSIS COMPLETE\n";