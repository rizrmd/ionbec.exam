<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "=== Fixing All Out of Sync Sequences ===\n\n";

    $tablesToFix = [
        'items' => ['max_id' => 723, 'target_seq' => 724],
        'questions' => ['max_id' => 1054, 'target_seq' => 1055],
        'exams' => ['max_id' => 42, 'target_seq' => 43],
        'categories' => ['max_id' => 41, 'target_seq' => 42],
        'deliveries' => ['max_id' => 150, 'target_seq' => 151],
        'users' => ['max_id' => 51, 'target_seq' => 52],
        'clients' => ['max_id' => 3, 'target_seq' => 4],
    ];

    foreach ($tablesToFix as $table => $data) {
        echo "🔧 Fixing {$table} table...\n";

        try {
            // Get actual current max to be safe
            $currentMax = DB::select("SELECT MAX(id) as max_id FROM {$table}")[0]->max_id ?? 0;
            $targetSeq = $currentMax + 1;

            echo "  - Current MAX ID: {$currentMax}\n";
            echo "  - Target sequence: {$targetSeq}\n";

            // Reset sequence
            DB::statement("ALTER SEQUENCE {$table}_id_seq RESTART WITH {$targetSeq}");

            // Verify
            $newSeq = DB::select("SELECT last_value FROM {$table}_id_seq")[0]->last_value;
            echo "  - New sequence: {$newSeq}\n";
            echo "  - Status: ✅ FIXED\n";

        } catch (Exception $e) {
            echo "  - Error: " . $e->getMessage() . "\n";
            echo "  - Status: ❌ FAILED\n";
        }
        echo "\n";
    }

    echo "🎉 All sequences have been processed!\n";
    echo "\n✅ Sequence fix completed!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}