<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Check current max ID in items table
    $maxIdResult = DB::select('SELECT MAX(id) as max_id FROM items');
    $maxId = $maxIdResult[0]->max_id ?? 0;
    echo "Current MAX ID in items table: " . $maxId . "\n";

    // Check current sequence value
    $seqResult = DB::select("SELECT last_value FROM items_id_seq");
    $currentSeq = $seqResult[0]->last_value;
    echo "Current sequence value: " . $currentSeq . "\n";

    // Check if there are any gaps
    $existingIds = DB::select("SELECT id FROM items ORDER BY id");
    echo "\nExisting IDs: ";
    $ids = [];
    foreach ($existingIds as $item) {
        $ids[] = $item->id;
        echo $item->id . " ";
    }
    echo "\n";

    // Find gaps
    $expectedId = 1;
    $gaps = [];
    foreach ($ids as $actualId) {
        while ($expectedId < $actualId) {
            $gaps[] = $expectedId;
            $expectedId++;
        }
        $expectedId++;
    }

    if (!empty($gaps)) {
        echo "Gaps found: " . implode(', ', $gaps) . "\n";
    } else {
        echo "No gaps found.\n";
    }

    // Reset sequence to max_id + 1
    $newSeqValue = $maxId + 1;
    echo "\nResetting sequence to: " . $newSeqValue . "\n";

    DB::statement("ALTER SEQUENCE items_id_seq RESTART WITH {$newSeqValue}");

    // Verify the reset
    $newSeqResult = DB::select("SELECT last_value FROM items_id_seq");
    echo "New sequence value: " . $newSeqResult[0]->last_value . "\n";

    echo "\n✅ Sequence synchronization completed successfully!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}