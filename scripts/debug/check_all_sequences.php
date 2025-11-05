<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "=== Checking All Table Sequences ===\n\n";

    // Check items table
    echo "📋 ITEMS TABLE:\n";
    $maxItems = DB::select('SELECT MAX(id) as max_id FROM items')[0]->max_id ?? 0;
    echo "  - MAX ID: " . $maxItems . "\n";

    try {
        $itemsSeq = DB::select("SELECT last_value FROM items_id_seq")[0]->last_value;
        echo "  - Sequence: " . $itemsSeq . "\n";
        echo "  - Status: " . (($itemsSeq == $maxItems + 1) ? "✅ OK" : "❌ OUT OF SYNC") . "\n";
    } catch (Exception $e) {
        echo "  - Sequence: ERROR - " . $e->getMessage() . "\n";
    }
    echo "\n";

    // Check questions table
    echo "📋 QUESTIONS TABLE:\n";
    $maxQuestions = DB::select('SELECT MAX(id) as max_id FROM questions')[0]->max_id ?? 0;
    echo "  - MAX ID: " . $maxQuestions . "\n";

    try {
        $questionsSeq = DB::select("SELECT last_value FROM questions_id_seq")[0]->last_value;
        echo "  - Sequence: " . $questionsSeq . "\n";
        echo "  - Status: " . (($questionsSeq == $maxQuestions + 1) ? "✅ OK" : "❌ OUT OF SYNC") . "\n";
    } catch (Exception $e) {
        echo "  - Sequence: ERROR - " . $e->getMessage() . "\n";
    }
    echo "\n";

    // Check other potential tables with sequences
    $tablesToCheck = [
        'exams',
        'categories',
        'deliveries',
        'attempts',
        'users',
        'clients'
    ];

    foreach ($tablesToCheck as $table) {
        echo "📋 " . strtoupper($table) . " TABLE:\n";
        try {
            $maxId = DB::select("SELECT MAX(id) as max_id FROM {$table}")[0]->max_id ?? 0;
            echo "  - MAX ID: " . $maxId . "\n";

            try {
                $seqResult = DB::select("SELECT last_value FROM {$table}_id_seq")[0]->last_value;
                echo "  - Sequence: " . $seqResult . "\n";
                echo "  - Status: " . (($seqResult == $maxId + 1) ? "✅ OK" : "❌ OUT OF SYNC") . "\n";
            } catch (Exception $e) {
                echo "  - Sequence: N/A (No sequence or error)\n";
            }
        } catch (Exception $e) {
            echo "  - ERROR: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }

    // Check recent error logs if they exist
    echo "📋 RECENT ACTIVITIES:\n";
    try {
        $recentItems = DB::select("SELECT id, title, created_at FROM items ORDER BY created_at DESC LIMIT 5");
        echo "  - Recent items:\n";
        foreach ($recentItems as $item) {
            echo "    * ID:{$item->id} - {$item->title} ({$item->created_at})\n";
        }
    } catch (Exception $e) {
        echo "  - Error checking recent items: " . $e->getMessage() . "\n";
    }

    echo "\n✅ Sequence check completed!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}