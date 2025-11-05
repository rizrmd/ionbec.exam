<?php

/**
 * DEBUG VIGNETTE QUESTIONS - SIMPLE LARAVEL VERSION
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUGGING VIGNETTE QUESTIONS FOR DELIVERY 22 ===\n\n";

use App\Models\Deliveries\Delivery;
use App\Models\Exams\Exam;
use App\Models\Exams\Item;
use App\Models\Exams\Question;

try {

    // Get delivery 22
    $delivery = Delivery::find(22);
    if (!$delivery) {
        echo "❌ Delivery 22 not found\n";
        exit;
    }

    echo "✅ Delivery 22 found\n";
    echo "Name: {$delivery->name}\n";
    echo "Exam ID: {$delivery->exam_id}\n";
    echo "Duration: {$delivery->duration} minutes\n\n";

    // Get exam
    $exam = $delivery->exam;
    if (!$exam) {
        echo "❌ Exam not found for delivery 22\n";
        exit;
    }

    echo "✅ Exam {$exam->id} found: {$exam->name}\n\n";

    // Get all items for this exam
    echo "=== EXAM ITEMS FOR EXAM {$exam->id} ===\n";
    $items = $exam->items()->withPivot('order')->orderBy('order')->get();

    foreach ($items as $index => $item) {
        echo "Item #" . ($index + 1) . "\n";
        echo "ID: {$item->id}\n";
        echo "Hash: {$item->hash}\n";
        echo "Title: " . substr($item->title, 0, 50) . "...\n";
        echo "Is Vignette: " . ($item->is_vignette ? 'YES' : 'NO') . "\n";

        // Get questions count
        $questionCount = $item->questions()->count();
        echo "Question Count: {$questionCount}\n";

        if ($item->is_vignette) {
            echo "Vignette Content (first 200 chars): " . substr(strip_tags($item->content), 0, 200) . "...\n";

            // Get questions for this vignette item
            $questions = $item->questions()->with('answers')->get();
            echo "Questions in vignette:\n";

            foreach ($questions as $qIndex => $question) {
                echo "  " . ($qIndex + 1) . ". Question ID: {$question->id}\n";
                echo "     Question Hash: {$question->hash}\n";
                echo "     Item Hash: " . ($question->item_hash ?: 'NULL') . "\n";
                echo "     Question Text: " . substr(strip_tags($question->question), 0, 100) . "...\n";
                echo "     Answer Count: " . $question->answers->count() . "\n";

                if ($question->answers->count() > 0) {
                    echo "     Answers:\n";
                    foreach ($question->answers as $aIndex => $answer) {
                        echo "       " . ($aIndex + 1) . ". " . substr(strip_tags($answer->answer), 0, 50) . "...\n";
                    }
                }
            }
        }
        echo "\n" . str_repeat("-", 80) . "\n\n";
    }

    // Check for questions with missing item_hash
    echo "=== QUESTIONS WITH MISSING ITEM_HASH ===\n";
    $allQuestionIds = $items->pluck('id')->toArray();
    $questionsWithNullHash = Question::whereIn('item_id', $allQuestionIds)
        ->where(function($query) {
            $query->whereNull('item_hash')
                  ->orWhere('item_hash', '');
        })
        ->with('item')
        ->get();

    if ($questionsWithNullHash->count() > 0) {
        foreach ($questionsWithNullHash as $question) {
            echo "Question ID: {$question->id}\n";
            echo "Hash: {$question->hash}\n";
            echo "Item Hash: " . ($question->item_hash ?: 'NULL') . "\n";
            echo "Item Title: " . substr($question->item->title, 0, 50) . "...\n";
            echo "Is Vignette Item: " . ($question->item->is_vignette ? 'YES' : 'NO') . "\n";
            echo "Question Text: " . substr(strip_tags($question->question), 0, 100) . "...\n\n";
        }
    } else {
        echo "✅ All questions have item_hash populated\n\n";
    }

    // Check snapshot data
    echo "=== SNAPSHOT DATA FOR DELIVERY 22 ===\n";
    if ($delivery->snapshot) {
        echo "✅ Snapshot found\n";
        echo "Total Items: {$delivery->total_items}\n";
        echo "Total Questions: {$delivery->total_questions}\n";

        $snapshot = json_decode($delivery->snapshot, true);
        if ($snapshot && isset($snapshot['exam_structure']['items'])) {
            echo "Snapshot items count: " . count($snapshot['exam_structure']['items']) . "\n";

            foreach ($snapshot['exam_structure']['items'] as $index => $snapshotItem) {
                if ($snapshotItem['is_vignette'] ?? false) {
                    echo "Vignette Item #" . ($index + 1) . ":\n";
                    echo "  Hash: {$snapshotItem['hash']}\n";
                    echo "  Title: " . substr($snapshotItem['title'], 0, 50) . "...\n";
                    echo "  Questions count: " . count($snapshotItem['questions'] ?? []) . "\n";

                    if (isset($snapshotItem['questions'])) {
                        foreach ($snapshotItem['questions'] as $qIndex => $question) {
                            echo "    Question " . ($qIndex + 1) . ": " . substr(strip_tags($question['question']), 0, 80) . "...\n";
                            echo "    Item Hash: " . ($question['item_hash'] ?? 'NULL') . "\n";
                        }
                    }
                    echo "\n";
                }
            }
        }
    } else {
        echo "❌ No snapshot found for delivery 22\n\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";