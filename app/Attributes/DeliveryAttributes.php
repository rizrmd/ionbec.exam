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

    public function getQuestionsCountAttribute()
    {
        if (!$this->exam_id) {
            return 0;
        }

        return $this->exam instanceof Exam ? $this->exam->totalQuestions() : 0;
    }

    public function getIsInterviewAttribute()
    {
        return $this->exam ? $this->exam->is_interview : false;
    }
}
