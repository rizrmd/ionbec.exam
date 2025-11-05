<?php

// Direct script to fix corrupt dates and create snapshots
// This bypasses Eloquent to avoid Carbon issues

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== FIXING CORRUPT DATE DATA AND CREATING SNAPSHOTS ===\n\n";

// First, let's check delivery 18's date data
$delivery18 = DB::table('deliveries')->where('id', 18)->first();

if ($delivery18) {
    echo "Delivery 18 data:\n";
    echo "  scheduled_at: " . var_export($delivery18->scheduled_at, true) . "\n";
    echo "  ended_at: " . var_export($delivery18->ended_at, true) . "\n";
    echo "  is_finished: " . var_export($delivery18->is_finished, true) . "\n\n";
}

// For now, let's just create snapshots bypassing the corrupt date issue
// We'll use the ExamSnapshotService but load delivery data manually

$snapshotService = app(\App\Services\ExamSnapshotService::class);

// Get deliveries that need snapshots (raw query)
$deliveryIds = DB::table('deliveries')
    ->whereNotExists(function ($q) {
        $q->select(DB::raw(1))
          ->from('delivery_snapshots')
          ->whereColumn('delivery_snapshots.delivery_id', 'deliveries.id');
    })
    ->pluck('id');

echo "Found {$deliveryIds->count()} deliveries without snapshots\n\n";

$created = 0;
$failed = 0;
$errors = [];

foreach ($deliveryIds as $deliveryId) {
    try {
        // Get raw delivery data
        $deliveryData = DB::table('deliveries')->where('id', $deliveryId)->first();

        if (!$deliveryData) {
            continue;
        }

        // Get exam data
        $examData = DB::table('exams')->where('id', $deliveryData->exam_id)->first();

        if (!$examData) {
            echo "  ⊗ Skipped Delivery ID {$deliveryId}: No exam found\n";
            continue;
        }

        // Build exam structure manually
        $items = DB::table('items')
            ->join('exam_item', 'items.id', '=', 'exam_item.item_id')
            ->where('exam_item.exam_id', $examData->id)
            ->select('items.*', 'exam_item.order')
            ->orderBy('exam_item.order')
            ->get();

        $totalQuestions = 0;
        $itemsData = [];

        foreach ($items as $item) {
            $questions = DB::table('questions')
                ->where('item_id', $item->id)
                ->orderBy('order')
                ->get();

            $questionsData = [];
            foreach ($questions as $question) {
                $answers = DB::table('answers')
                    ->where('question_id', $question->id)
                    ->get();

                $answersData = [];
                foreach ($answers as $answer) {
                    $answersData[] = [
                        'id' => $answer->id,
                        'question_id' => $answer->question_id,
                        'answer' => $answer->answer,
                        'is_correct_answer' => $answer->is_correct_answer,
                    ];
                }

                $questionsData[] = [
                    'id' => $question->id,
                    'item_id' => $question->item_id,
                    'type' => $question->type,
                    'question' => $question->question,
                    'is_random' => $question->is_random,
                    'order' => $question->order,
                    'score' => $question->score,
                    'answers' => $answersData,
                ];

                $totalQuestions++;
            }

            $attachments = DB::table('attachables')
                ->join('attachments', 'attachables.attachment_id', '=', 'attachments.id')
                ->where('attachables.attachable_type', 'App\\Models\\Exams\\Item')
                ->where('attachables.attachable_id', $item->id)
                ->select('attachments.*')
                ->get();

            $attachmentsData = [];
            foreach ($attachments as $attachment) {
                $attachmentsData[] = [
                    'id' => $attachment->id,
                    'path' => $attachment->path,
                    'mime' => $attachment->mime,
                ];
            }

            $itemsData[] = [
                'id' => $item->id,
                'title' => $item->title,
                'type' => $item->type,
                'content' => $item->content,
                'is_vignette' => $item->is_vignette,
                'is_random' => $item->is_random,
                'order' => $item->order,
                'questions' => $questionsData,
                'attachments' => $attachmentsData,
            ];
        }

        $examStructure = [
            'exam_id' => $examData->id,
            'exam_code' => $examData->code,
            'exam_name' => $examData->name,
            'is_random' => $examData->is_random,
            'is_mcq' => $examData->is_mcq,
            'is_interview' => $examData->is_interview,
            'items' => $itemsData,
            'metadata' => [
                'total_items' => count($itemsData),
                'total_questions' => $totalQuestions,
                'snapshot_created_at' => now()->toIso8601String(),
            ],
        ];

        // Insert snapshot directly
        DB::table('delivery_snapshots')->insert([
            'delivery_id' => $deliveryId,
            'exam_id' => $examData->id,
            'exam_structure' => json_encode($examStructure),
            'total_questions' => $totalQuestions,
            'total_items' => count($itemsData),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $created++;
        echo "  ✓ Created snapshot for Delivery ID {$deliveryId}: {$deliveryData->name} ({$totalQuestions} questions)\n";

    } catch (\Exception $e) {
        $failed++;
        $errors[] = "Delivery ID {$deliveryId}: " . $e->getMessage();
        echo "  ✗ Failed for Delivery ID {$deliveryId}: {$e->getMessage()}\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "Created: {$created}\n";
echo "Failed: {$failed}\n";

if (!empty($errors)) {
    echo "\n=== ERRORS ===\n";
    foreach ($errors as $error) {
        echo $error . "\n";
    }
}
