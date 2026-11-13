<?php

namespace App\Attributes;

use Carbon\Carbon;
use App\Models\Exams\Exam;
use App\Models\Takers\Group;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\DB;

/**
 * @property string $status
 * @property bool   $takers_ready
 * @property int    $takers_count
 * @property int    $questions_count
 */
trait DeliveryAttributes
{
    public function endedAt(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $newValue = $value;
                if ($this->is_anytime && null !== $value) {
                    $newValue = Carbon::parse($value);
                }
                if (! $this->is_anytime) {
                    $newValue = Carbon::parse($this->scheduled_at)->addMinutes($this->duration);
                }

                return $newValue?->format('Y-m-d H:i');
            }
        );
    }

    public function getStatusAttribute()
    {
        return self::getStatus($this);
    }

    public function getTakersReadyAttribute()
    {
        return $this->takers()->count() > 0;
    }

    public function getTakersCountAttribute()
    {
        if (!$this->group_id) {
            return 0;
        }

        // Use direct DB query to bypass ClientScope issues
        return DB::table('group_taker')
            ->where('group_id', $this->group_id)
            ->count();
    }

    /**
     * Frozen-content info for the back office (not appended by default; call ->append('snapshot_info')).
     *
     * A scheduled delivery serves the exam content frozen in its snapshot when it was created.
     * Editing the exam afterwards (attach/detach question sets) does not change that delivery.
     *
     * @return array{frozen: bool, frozen_at: ?string, snapshot_items: int, live_exam_items: int, differs_from_exam: bool}
     */
    public function getSnapshotInfoAttribute(): array
    {
        $snapshot = $this->relationLoaded('snapshot') ? $this->snapshot : $this->snapshot()->first();
        $liveItemIds = $this->exam_id
            ? DB::table('exam_item')->where('exam_id', $this->exam_id)->pluck('item_id')->map(fn ($id) => (int) $id)->sort()->values()->all()
            : [];

        if (! $snapshot) {
            return [
                'frozen' => false,
                'frozen_at' => null,
                'snapshot_items' => 0,
                'live_exam_items' => count($liveItemIds),
                'differs_from_exam' => false,
            ];
        }

        $snapshotItemIds = collect($snapshot->exam_structure['items'] ?? [])
            ->pluck('id')->filter()->map(fn ($id) => (int) $id)->sort()->values()->all();

        return [
            'frozen' => true,
            'frozen_at' => $snapshot->created_at?->format('Y-m-d H:i'),
            'snapshot_items' => count($snapshotItemIds),
            'live_exam_items' => count($liveItemIds),
            'differs_from_exam' => $snapshotItemIds !== $liveItemIds,
        ];
    }

    public function getQuestionsCountAttribute()
    {
        if (!$this->exam_id) {
            return 0;
        }

        // Deliveries with a snapshot contain exactly the snapshot items (what participants
        // see and what scoring counts), even if the exam was edited afterwards.
        $itemSource = app(\App\Services\DeliveryItemSource::class);
        if ($itemSource->snapshotItems($this)->isNotEmpty()) {
            return $itemSource->questionCount($this);
        }

        return $this->exam instanceof Exam ? $this->exam->totalQuestions() : 0;
    }

    public function getIsInterviewAttribute()
    {
        return $this->exam ? $this->exam->is_interview : false;
    }
}
