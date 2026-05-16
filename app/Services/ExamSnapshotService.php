<?php

namespace App\Services;

use App\Models\Exams\Exam;
use App\Models\Exams\Item;
use App\Models\Exams\Question;
use App\Models\Deliveries\Delivery;
use App\Models\Deliveries\DeliverySnapshot;
use App\Scopes\ClientScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Exam Snapshot Service
 *
 * Handles creating and managing exam snapshots for deliveries.
 * Ensures exam integrity by freezing the exam structure when a delivery is created.
 */
class ExamSnapshotService
{
    /**
     * Create a snapshot for a delivery.
     *
     * @param Delivery $delivery
     * @return DeliverySnapshot
     */
    public function createSnapshot(Delivery $delivery): DeliverySnapshot
    {
        // Check if snapshot already exists
        if ($delivery->snapshot) {
            Log::info('Snapshot already exists for delivery', ['delivery_id' => $delivery->id]);
            return $delivery->snapshot;
        }

        $exam = Exam::withoutGlobalScope(ClientScope::class)->find($delivery->exam_id);
        if (!$exam) {
            throw new \Exception("Exam not found for delivery {$delivery->id}");
        }

        // Build exam structure
        $examStructure = $this->buildExamStructure($exam);

        // Create snapshot
        $snapshot = DeliverySnapshot::create([
            'delivery_id' => $delivery->id,
            'exam_id' => $exam->id,
            'exam_structure' => $examStructure,
            'total_questions' => $examStructure['metadata']['total_questions'],
            'total_items' => $examStructure['metadata']['total_items'],
        ]);

        Log::info('Created delivery snapshot', [
            'delivery_id' => $delivery->id,
            'exam_id' => $exam->id,
            'total_questions' => $snapshot->total_questions,
            'total_items' => $snapshot->total_items,
        ]);

        return $snapshot;
    }

    /**
     * Build the complete exam structure for snapshot.
     *
     * @param Exam $exam
     * @return array
     */
    protected function buildExamStructure(Exam $exam): array
    {
        // Load items with proper ordering
        $items = Item::withoutGlobalScope(\App\Scopes\ClientScope::class)
            ->join('exam_item', 'items.id', '=', 'exam_item.item_id')
            ->where('exam_item.exam_id', $exam->id)
            ->select('items.*', 'exam_item.order')
            ->orderBy('exam_item.order')
            ->get();

        $totalQuestions = 0;
        $itemsData = [];

        foreach ($items as $item) {
            // Load questions for this item
            $questions = Question::withoutGlobalScope(\App\Scopes\ClientScope::class)
                ->where('item_id', $item->id)
                ->with(['answers' => function ($q) {
                    $q->withoutGlobalScope(\App\Scopes\ClientScope::class);
                }])
                ->orderBy('order')
                ->get();

            $questionsData = [];
            foreach ($questions as $question) {
                $answersData = [];
                foreach ($question->answers as $answer) {
                    $answersData[] = [
                        'id' => $answer->id,
                        'hash' => $answer->getRawOriginal('hash') ?: $answer->hash,
                        'question_id' => $answer->question_id,
                        'answer' => $answer->answer,
                        'is_correct_answer' => $answer->is_correct_answer,
                    ];
                }

                $questionsData[] = [
                    'id' => $question->id,
                    'hash' => $question->getRawOriginal('hash') ?: $question->hash,
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

            // Load attachments (polymorphic relationship)
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
                'hash' => $item->getRawOriginal('hash') ?: $item->hash,
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

        return [
            'exam_id' => $exam->id,
            'exam_code' => $exam->code,
            'exam_name' => $exam->name,
            'is_random' => $exam->is_random,
            'is_mcq' => $exam->is_mcq,
            'is_interview' => $exam->is_interview,
            'items' => $itemsData,
            'metadata' => [
                'total_items' => count($itemsData),
                'total_questions' => $totalQuestions,
                'snapshot_created_at' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * Get or create snapshot for a delivery.
     *
     * @param Delivery $delivery
     * @return DeliverySnapshot
     */
    public function getOrCreateSnapshot(Delivery $delivery): DeliverySnapshot
    {
        if ($delivery->snapshot) {
            return $delivery->snapshot;
        }

        return $this->createSnapshot($delivery);
    }

    /**
     * Refresh snapshot for a delivery (recreate with current exam state).
     * Use with caution - this will affect ongoing deliveries!
     *
     * @param Delivery $delivery
     * @return DeliverySnapshot
     */
    public function refreshSnapshot(Delivery $delivery): DeliverySnapshot
    {
        // Delete existing snapshot
        if ($delivery->snapshot) {
            $delivery->snapshot->delete();
            $delivery->unsetRelation('snapshot');
        }

        // Create new snapshot
        return $this->createSnapshot($delivery->fresh());
    }
}
