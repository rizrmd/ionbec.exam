<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "🔍 CHECKING DELIVERY SNAPSHOT\n";
echo "==========================\n\n";

$delivery = \App\Models\Deliveries\Delivery::find(71);

if (!$delivery) {
    echo "❌ Delivery ID 71 not found\n";
    exit;
}

echo "✅ Found Delivery: " . $delivery->name . "\n";

if ($delivery->snapshot) {
    echo "✅ Has Snapshot: YES\n";
    echo "📊 Snapshot Info:\n";
    echo "   Total Questions: " . ($delivery->snapshot->total_questions ?? 'N/A') . "\n";
    echo "   Delivery Status: " . $delivery->status . "\n";
    echo "   Is Finished: " . ($delivery->is_finished ? 'Yes' : 'No') . "\n";
    echo "   Created: " . $delivery->created_at . "\n";

    if ($delivery->snapshot->exam_structure) {
        $structure = $delivery->snapshot->exam_structure;
        if (isset($structure['items'])) {
            echo "   Items in Snapshot: " . count($structure['items']) . "\n";

            $itemCount = 0;
            $questionCount = 0;

            foreach ($structure['items'] as $item) {
                $itemCount++;
                if (isset($item['questions'])) {
                    $questionCount += count($item['questions']);
                }
            }

            echo "   Total Items: " . $itemCount . "\n";
            echo "   Total Questions: " . $questionCount . "\n";

            if ($itemCount > 0) {
                echo "\n📄 Sample Items:\n";
                $shown = 0;
                foreach ($structure['items'] as $item) {
                    if ($shown >= 3) break;
                    echo "   Item " . ($shown + 1) . ": " . ($item['title'] ?? 'No Title') . "\n";
                    if (isset($item['questions'])) {
                        echo "     Questions: " . count($item['questions']) . "\n";
                    }
                    $shown++;
                }
            }
        } else {
            echo "   ❌ No items in exam structure!\n";
        }
    } else {
        echo "   ❌ No exam structure in snapshot!\n";
    }
} else {
    echo "❌ No Snapshot Found\n";
}

echo "\n🎯 SNAPSHOT ANALYSIS COMPLETE\n";