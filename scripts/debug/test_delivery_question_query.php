<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Illuminate\Http\Request;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\BackOffice\DeliveryController;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Create request with the same parameters as the curl
$request = new Request();
$request->merge([
    'page' => 1,
    'query' => 'A 79 years old woman',
    'perPage' => 15
]);

$delivery_hash = '9OGqXvLw';

echo "=== Testing Delivery Question Query ===\n";
echo "Delivery Hash: $delivery_hash\n";
echo "Search Query: " . $request->input('query') . "\n\n";

try {
    // Find delivery by hash
    $delivery = Delivery::byHash($delivery_hash);
    if (!$delivery) {
        echo "ERROR: Delivery not found with hash: $delivery_hash\n";

        // Let's try to find a delivery to test with
        $testDelivery = Delivery::orderBy('id', 'desc')->first();
        if ($testDelivery) {
            echo "Using test delivery ID: " . $testDelivery->id . "\n";
            $delivery = $testDelivery;
        } else {
            echo "No deliveries found in database\n";
            exit(1);
        }
    }

    echo "Using Delivery ID: " . $delivery->id . "\n";
    echo "Exam ID: " . $delivery->exam_id . "\n\n";

    // Simulate getBaseDataDetail method to get question IDs
    $items = \App\Models\Exams\Item::withoutGlobalScope(\App\Scopes\ClientScope::class)
        ->join('exam_item', 'items.id', '=', 'exam_item.item_id')
        ->where('exam_item.exam_id', $delivery->exam_id)
        ->with(['questions' => function ($q) {
            $q->withoutGlobalScope(\App\Scopes\ClientScope::class);
        }])
        ->select('items.*')
        ->get();

    $questions = [];
    foreach ($items as $item) {
        foreach ($item->questions as $question) {
            $questions[] = $question->id;
        }
    }

    echo "Total questions found: " . count($questions) . "\n";

    // Now query questions with search filter
    $questionQuery = \App\Models\Exams\Question::query()->whereIn('id', $questions);

    if ($request->input('query')) {
        $questionQuery->where('question', 'like', "%" . $request->input('query') . "%");
    }

    $results = $questionQuery->get(['id', 'item_id', 'question', 'score', 'order', 'hash']);

    echo "Questions matching search query: " . $results->count() . "\n\n";

    foreach ($results as $question) {
        $questionPreview = substr(strip_tags($question->question), 0, 100);
        echo "Question ID: {$question->id}\n";
        echo "Item ID: {$question->item_id}\n";
        echo "Preview: {$questionPreview}...\n";
        echo "Hash: {$question->hash}\n";
        echo "---\n";
    }

    // Check for duplicates by ID
    $questionIds = $results->pluck('id')->toArray();
    $uniqueIds = array_unique($questionIds);

    if (count($questionIds) !== count($uniqueIds)) {
        echo "\n⚠️  DUPLICATE QUESTION IDs DETECTED!\n";
        $duplicates = array_diff_assoc($questionIds, $uniqueIds);
        echo "Duplicate IDs: " . implode(', ', $duplicates) . "\n";
    } else {
        echo "\n✅ No duplicate question IDs found in query results\n";
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}