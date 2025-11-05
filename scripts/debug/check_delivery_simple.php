<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "🔍 CHECKING DELIVERY ID 71\n";
echo "==========================\n\n";

$delivery = \App\Models\Deliveries\Delivery::find(71);

if (!$delivery) {
    echo "❌ Delivery ID 71 not found\n";
    exit;
}

echo "✅ Found Delivery: " . $delivery->name . "\n";
echo "   Status: " . $delivery->status . "\n";
echo "   Is Finished: " . ($delivery->is_finished ? 'Yes' : 'No') . "\n";
echo "   Exam ID: " . $delivery->exam_id . "\n";
echo "   Created: " . $delivery->created_at . "\n\n";

// Check if delivery has snapshot
echo "📊 SNAPSHOT CHECK:\n";
if ($delivery->snapshot) {
    echo "✅ Has Snapshot: YES\n";

    $snapshotData = $delivery->snapshot->toArray();
    echo "   Snapshot Keys: " . implode(', ', array_keys($snapshotData)) . "\n";

    if (isset($snapshotData['exam_structure'])) {
        echo "✅ Has exam_structure\n";
        $structure = $snapshotData['exam_structure'];

        if (is_string($structure)) {
            echo "   - exam_structure is string, attempting to decode...\n";
            $decoded = json_decode($structure, true);
            if ($decoded) {
                $structure = $decoded;
                echo "   ✅ Successfully decoded JSON\n";
            } else {
                echo "   ❌ Failed to decode JSON\n";
            }
        }

        if (isset($structure['items']) && is_array($structure['items'])) {
            echo "   ✅ Items found: " . count($structure['items']) . "\n";

            if (count($structure['items']) > 0) {
                echo "   📄 Sample item data:\n";
                $firstItem = $structure['items'][0];
                echo "     - Title: " . ($firstItem['title'] ?? 'No Title') . "\n";
                echo "     - Questions: " . (isset($firstItem['questions']) ? count($firstItem['questions']) : 0) . "\n";
            }
        } else {
            echo "   ❌ No items in exam_structure\n";
        }
    } else {
        echo "   ❌ No exam_structure in snapshot\n";
    }
} else {
    echo "❌ No Snapshot Found\n";
}

echo "\n🎯 EXAM ITEMS CHECK:\n";
$exam = $delivery->exam;
if ($exam) {
    echo "✅ Found Exam: " . $exam->name . " (ID: " . $exam->id . ")\n";

    $items = $exam->items()->with('questions')->get();
    echo "   Items in exam: " . $items->count() . "\n";

    $totalQuestions = 0;
    foreach ($items as $item) {
        $totalQuestions += $item->questions->count();
    }
    echo "   Total questions: " . $totalQuestions . "\n";

    if ($items->count() > 0) {
        echo "   📄 Sample item:\n";
        $firstItem = $items->first();
        echo "     - Title: " . $firstItem->title . "\n";
        echo "     - Questions: " . $firstItem->questions->count() . "\n";
    }
} else {
    echo "❌ No exam found for delivery\n";
}

echo "\n🎯 ANALYSIS COMPLETE\n";