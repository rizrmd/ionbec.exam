<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\Deliveries\Delivery;
use App\Models\Exams\Exam;
use App\Models\Takers\Taker;

// Initialize Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG QUESTION BLANK ISSUE ===\n\n";

// Get a sample delivery
$delivery = Delivery::withoutGlobalScope(\App\Scopes\ClientScope::class)
    ->with('exam')
    ->first();

if (!$delivery) {
    echo "No delivery found!\n";
    exit;
}

echo "Delivery ID: {$delivery->id}\n";
echo "Exam ID: {$delivery->exam_id}\n";
echo "Client ID: {$delivery->client_id}\n\n";

// Check exam
$exam = $delivery->exam;
if (!$exam) {
    echo "Exam not found via relationship, trying direct query...\n";
    $exam = Exam::withoutGlobalScope(\App\Scopes\ClientScope::class)
        ->where('id', $delivery->exam_id)
        ->first();
}

if (!$exam) {
    echo "Exam not found at all!\n";
    exit;
}

echo "Exam found: {$exam->name} (ID: {$exam->id})\n\n";

// Check snapshot
$snapshot = $delivery->snapshot;
if ($snapshot) {
    echo "Snapshot found!\n";
    echo "Total questions in snapshot: {$snapshot->total_questions}\n";
    echo "Total items in snapshot: {$snapshot->total_items}\n";

    // Check exam structure
    if (isset($snapshot->exam_structure['items'])) {
        echo "Items in snapshot: " . count($snapshot->exam_structure['items']) . "\n";

        // Check first item structure
        $firstItem = isset($snapshot->exam_structure['items'][0]) ? $snapshot->exam_structure['items'][0] : null;
        if ($firstItem) {
            echo "First item ID: {$firstItem['id']}\n";
            echo "First item has questions: " . (isset($firstItem['questions']) ? 'YES' : 'NO') . "\n";

            if (isset($firstItem['questions'])) {
                echo "Questions count in first item: " . count($firstItem['questions']) . "\n";

                // Check first question
                $firstQuestion = isset($firstItem['questions'][0]) ? $firstItem['questions'][0] : null;
                if ($firstQuestion) {
                    echo "First question ID: {$firstQuestion['id']}\n";
                    echo "First question type: " . (isset($firstQuestion['type']) ? $firstQuestion['type'] : 'unknown') . "\n";
                    echo "First question has answers: " . (isset($firstQuestion['answers']) ? 'YES' : 'NO') . "\n";

                    if (isset($firstQuestion['answers'])) {
                        echo "Answers count: " . count($firstQuestion['answers']) . "\n";
                    }
                }
            }
        }
    } else {
        echo "No exam_structure[items] in snapshot!\n";
    }
} else {
    echo "No snapshot found!\n";

    // Check exam items directly
    echo "\nChecking exam items directly...\n";
    $items = $exam->items()->with('questions.answers')->orderByPivot('order')->get();
    echo "Items count: {$items->count()}\n";

    if ($items->count() > 0) {
        $firstItem = $items->first();
        echo "First item ID: {$firstItem->id}\n";
        echo "Questions count: {$firstItem->questions()->count()}\n";

        $firstQuestion = $firstItem->questions()->first();
        if ($firstQuestion) {
            echo "First question ID: {$firstQuestion->id}\n";
            echo "First question type: " . (isset($firstQuestion->type->name) ? $firstQuestion->type->name : 'no type') . "\n";
            echo "Answers count: {$firstQuestion->answers()->count()}\n";
        }
    }
}

// Check Rust service
echo "\n=== Testing Rust Service ===\n";
$rustService = new \App\Services\RustService();
$examData = $rustService->loadExamData($exam->id, $delivery->id, null);

if ($examData && $examData['success']) {
    echo "Rust service successful!\n";
    echo "Items count: " . count($examData['items']) . "\n";

    if (!empty($examData['items'])) {
        $firstItem = $examData['items'][0];
        echo "First item has questions: " . (isset($firstItem['questions']) ? 'YES' : 'NO') . "\n";

        if (isset($firstItem['questions']) && !empty($firstItem['questions'])) {
            $firstQuestion = $firstItem['questions'][0];
            echo "First question type: " . (isset($firstQuestion['type']) ? $firstQuestion['type'] : 'unknown') . "\n";
            echo "First question has answers: " . (isset($firstQuestion['answers']) ? 'YES' : 'NO') . "\n";
        }
    }
} else {
    echo "Rust service failed or returned no data\n";
}

echo "\n=== End Debug ===\n";