<?php

namespace App\Services;

use App\Models\Attachments\Attachment;
use App\Models\Deliveries\Delivery;
use App\Models\Exams\Answer;
use App\Models\Exams\Item;
use App\Models\Exams\Question;
use App\Scopes\ClientScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for "which items belong to a delivery".
 *
 * A delivery snapshot freezes the item list when the delivery is created and is
 * what participants see and what scoring counts (see CalculateScore). Back-office
 * screens, lazy question loading and progress counters must use the same list,
 * otherwise items edited, detached or re-created after scheduling disappear or
 * cannot be resolved. Items are identified by their stable numeric id; the
 * persisted items.hash column is not reliable (it is not written by the Item
 * model and older values were produced with a different key).
 */
class DeliveryItemSource
{
    /**
     * Ordered item ids for a delivery: snapshot first, live exam_item as fallback.
     */
    public function itemIds(Delivery $delivery): Collection
    {
        $snapshotItems = $this->snapshotItems($delivery);

        if ($snapshotItems->isNotEmpty()) {
            return $snapshotItems->pluck('id')->filter()->map(fn ($id) => (int) $id)->values();
        }

        return DB::table('exam_item')
            ->where('exam_id', $delivery->exam_id)
            ->orderBy('order')
            ->orderBy('item_id')
            ->pluck('item_id')
            ->map(fn ($id) => (int) $id)
            ->values();
    }

    /**
     * Live Item models of a delivery in snapshot order (deleted items are skipped).
     */
    public function items(Delivery $delivery, array $with = []): Collection
    {
        $ids = $this->itemIds($delivery);
        if ($ids->isEmpty()) {
            return collect();
        }

        $position = $ids->flip();

        return Item::withoutGlobalScope(ClientScope::class)
            ->whereIn('id', $ids->all())
            ->with($with)
            ->get()
            ->sortBy(fn (Item $item) => $position[$item->id] ?? PHP_INT_MAX)
            ->values();
    }

    /**
     * Number of live questions of the delivery's items (the set scoring evaluates).
     */
    public function questionCount(Delivery $delivery): int
    {
        $ids = $this->itemIds($delivery);
        if ($ids->isEmpty()) {
            return 0;
        }

        return DB::table('questions')->whereIn('item_id', $ids->all())->count();
    }

    /**
     * Raw snapshot item arrays (empty collection if the delivery has no snapshot).
     */
    public function snapshotItems(Delivery $delivery): Collection
    {
        $snapshot = $delivery->relationLoaded('snapshot') ? $delivery->snapshot : $delivery->snapshot()->first();
        if (! $snapshot) {
            return collect();
        }

        return collect($snapshot->exam_structure['items'] ?? []);
    }

    /**
     * Find the snapshot item a participant asked for.
     *
     * Accepts the stable item id sent by the exam page, the hash stored in the
     * snapshot, the "item_{id}" fallback hash, the hash computed by the
     * application (Item::idToHash) or the legacy items.hash column value. The
     * result is always restricted to items that belong to this delivery.
     */
    public function findSnapshotItem(Delivery $delivery, ?string $hash, ?int $itemId = null): ?array
    {
        $items = $this->snapshotItems($delivery);
        if ($items->isEmpty()) {
            return null;
        }

        $byId = fn (?int $id) => $id ? $items->first(fn ($item) => (int) ($item['id'] ?? 0) === $id) : null;

        if ($found = $byId($itemId)) {
            return $found;
        }

        if ($hash === null || $hash === '') {
            return null;
        }

        if ($found = $items->first(fn ($item) => ($item['hash'] ?? null) === $hash)) {
            return $found;
        }

        if (preg_match('/^item_(\d+)$/', $hash, $m) && ($found = $byId((int) $m[1]))) {
            return $found;
        }

        try {
            if ($found = $byId(Item::hashToId($hash))) {
                return $found;
            }
        } catch (\Throwable $e) {
            // not a hash produced with the application key
        }

        $legacyId = Item::withoutGlobalScope(ClientScope::class)
            ->whereIn('id', $items->pluck('id')->filter()->all())
            ->where('hash', $hash)
            ->value('id');

        return $byId($legacyId ? (int) $legacyId : null);
    }

    /**
     * Questions (with answers, without answer keys) and attachments for one snapshot item.
     *
     * Live rows of the snapshot item id are preferred because scoring and answer
     * storage work on them. When the item or all of its questions were deleted
     * after the delivery was created, the frozen snapshot content is returned with
     * is_archived = true so the page can show it read-only instead of a blank page.
     *
     * @return array{questions: Collection, attachments: array, archived: bool, item_id: int}
     */
    public function questionsForSnapshotItem(array $snapshotItem): array
    {
        $itemId = (int) ($snapshotItem['id'] ?? 0);

        $item = Item::withoutGlobalScope(ClientScope::class)->with('attachments')->find($itemId);

        $questions = $item
            ? Question::withoutGlobalScope(ClientScope::class)
                ->where('item_id', $itemId)
                ->with(['answers'])
                ->orderBy('order')
                ->orderBy('id')
                ->get()
            : collect();

        $archived = false;
        if ($questions->isEmpty() && ! empty($snapshotItem['questions'])) {
            $questions = $this->archivedQuestions($snapshotItem['questions']);
            $archived = true;
        }

        $questions->each(function ($question) {
            $question->answers->each(function ($answer) {
                unset($answer->is_correct_answer);
                $answer->makeHidden('is_correct_answer');
            });
        });

        $attachments = $item && $item->attachments->isNotEmpty()
            ? $item->attachments->values()->all()
            : $this->snapshotAttachments($snapshotItem['attachments'] ?? []);

        return [
            'item_id' => $itemId,
            'questions' => $questions->values(),
            'attachments' => $attachments,
            'archived' => $archived,
        ];
    }

    /**
     * Snapshot attachments carry id/path/mime only; give them the stream URL.
     */
    public function snapshotAttachments(array $attachments): array
    {
        return collect($attachments)->map(function ($attachment) {
            $attachment = (array) $attachment;
            if (! empty($attachment['id']) && empty($attachment['url'])) {
                $attachment['url'] = route('attachment.stream', $attachment['id']);
            }

            return $attachment;
        })->values()->all();
    }

    /**
     * Unsaved models built from frozen snapshot data so they serialise exactly
     * like live questions (same hash accessor, same type cast).
     */
    private function archivedQuestions(array $questions): Collection
    {
        return collect($questions)->map(function ($data) {
            $data = (array) $data;
            // Snapshot stores QuestionType as {name, label, ...}; the model expects its name.
            $type = $data['type'] ?? 'multiple-choice';
            if (is_array($type) || is_object($type)) {
                $type = ((array) $type)['name'] ?? ((array) $type)['value'] ?? 'multiple-choice';
            }
            $data['type'] = $type;
            $question = new Question();
            $question->forceFill([
                'id' => $data['id'] ?? null,
                'item_id' => $data['item_id'] ?? null,
                'type' => $data['type'] ?? 'multiple-choice',
                'question' => $data['question'] ?? null,
                'is_random' => (bool) ($data['is_random'] ?? false),
                'order' => $data['order'] ?? 0,
                'score' => $data['score'] ?? 0,
            ]);
            $question->setAttribute('is_archived', true);

            $answers = collect($data['answers'] ?? [])->map(function ($answerData) {
                $answerData = (array) $answerData;
                $answer = new Answer();
                $answer->forceFill([
                    'id' => $answerData['id'] ?? null,
                    'question_id' => $answerData['question_id'] ?? null,
                    'answer' => $answerData['answer'] ?? null,
                ]);

                return $answer;
            });
            $question->setRelation('answers', $answers);

            return $question;
        });
    }
}
