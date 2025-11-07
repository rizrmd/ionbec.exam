<?php

namespace App\Models\Attempts;

use Carbon\Carbon;
use App\Models\Exams\Exam;
use App\Models\Exams\Item;
use App\Models\Takers\Taker;
use App\Models\Exams\Question;
use App\Traits\BelongsToClient;
use App\Traits\HasTakerCode;
use App\Traits\AutoPopulateHash;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\Deliveries\Delivery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Veelasky\LaravelHashId\Eloquent\HashableId;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @property Carbon                                   $expired_at
 * @property Delivery                                 $delivery
 * @property Carbon                                   $started_at
 * @property \Illuminate\Database\Eloquent\Collection $questions
 * @property string                                   $attempted_by
 * @property string                                   $exam_id
 * @property string                                   $id
 * @property float                                    $progress
 * @property float                                    $score
 */
class Attempt extends Model
{
    use HashableId;
    use BelongsToClient;
    use HasTakerCode;
    use AutoPopulateHash;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attempts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'attempted_by',
        'exam_id',
        'delivery_id',
        'ip_address',
        'started_at',
        'extra_minute',
        'ended_at',
        'finish_scoring',
        'client_id',
    ];

    protected $hidden = [
        'id',
    ];

    protected $appends = [
        'hash',
        'taker_code',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'extra_minute' => 'integer',
    ];

    /**
     * Define `belongsTo` relationship with Taker model.
     *
     * @return Relations\BelongsTo
     */
    public function taker(): Relations\BelongsTo
    {
        return $this->belongsTo(Taker::class, 'attempted_by', 'id')
            ->where('takers.client_id', $this->client_id)
            ->withoutGlobalScope(\App\Scopes\ClientScope::class);
    }

    /**
     * Alias for taker relationship (used in RootDashboardController)
     *
     * @return Relations\BelongsTo
     */
    public function attemptedBy(): Relations\BelongsTo
    {
        return $this->belongsTo(Taker::class, 'attempted_by', 'id');
    }

    /**
     * Define `belongsTo` relationship with Exam model.
     *
     * @return Relations\BelongsTo
     */
    public function exam(): Relations\BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id', 'id')
            ->where('exams.client_id', $this->client_id)
            ->withoutGlobalScope(\App\Scopes\ClientScope::class);
    }

    /**
     * define `belongsTo` relationship with Delivery model.
     *
     * @return Relations\BelongsTo
     */
    public function delivery(): Relations\BelongsTo
    {
        return $this->belongsTo(Delivery::class, 'delivery_id', 'id')
            ->where('deliveries.client_id', $this->client_id)
            ->withoutGlobalScope(\App\Scopes\ClientScope::class);
    }

    public function takerCode(): Attribute
    {
        return new Attribute(
            get: function () {
                $delivery = $this->delivery;
                if (!$delivery) {
                    return 'UNKNOWN-000';
                }
                
                // Use direct DB query to avoid ClientScope issues
                $groupData = \DB::table('groups')->where('id', $delivery->group_id)->first();
                $groupCode = $groupData ? $groupData->code : 'UNKNOWN';
                
                return self::getFormattedTakerCode(
                    $this->attempted_by,
                    $delivery->group_id,
                    $groupCode
                );
            },
        );
    }

    /**
     * Define  `belongsToMany` relationship with Question model.
     *
     * @return Relations\BelongsToMany
     */
    public function questions(): Relations\BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'attempt_question', 'attempt_id', 'question_id')->withPivot([
            'answer_hash', 'answer', 'is_correct', 'score',
        ]);
    }

    public function attemptQuestions(): Relations\HasMany
    {
        return $this->hasMany(AttemptQuestion::class, 'attempt_id', 'id');
    }

    /**
     * Determine if this attempt has expired.
     *
     * @return bool
     */
    public function getIsExpiredAttribute(): bool
    {
        return Carbon::now()->gt($this->expired_at);
    }

    /**
     * Check when this attempt should expire.
     *
     * @return Carbon
     */
    public function getExpiredAtAttribute(): Carbon
    {
        $delivery = $this->delivery;
        if (!$delivery) {
            // Default to 2 hours if delivery not found
            return $this->started_at->addHours(2);
        }

        if ($delivery->is_anytime) {
            return $this->started_at->addMinutes($delivery->duration + $this->extra_minute);
        }

        // 🔒 CRITICAL FIX: Handle automatic_start correctly
        // For automatic_start deliveries, ALWAYS use actual start time when user starts
        // The scheduled_at is just the earliest possible start time, not the timer start
        if ($delivery->automatic_start) {
            \Log::info('CRITICAL FIX: Automatic start timing calculation', [
                'attempt_id' => $this->id,
                'delivery_id' => $delivery->id,
                'automatic_start' => $delivery->automatic_start,
                'scheduled_at' => $delivery->scheduled_at,
                'started_at' => $this->started_at->toDateTimeString(),
                'duration' => $delivery->duration,
                'extra_minute' => $this->extra_minute,
                'calculated_expires_at' => $this->started_at->addMinutes($delivery->duration + $this->extra_minute)->toDateTimeString()
            ]);

            return $this->started_at->addMinutes($delivery->duration + $this->extra_minute);
        }

        // For manual_start deliveries (not automatic_start and not anytime),
        // the exam duration starts when the user actually starts the exam
        \Log::info('CRITICAL FIX: Manual start timing calculation', [
            'attempt_id' => $this->id,
            'delivery_id' => $delivery->id,
            'automatic_start' => $delivery->automatic_start ?? false,
            'is_anytime' => $delivery->is_anytime,
            'started_at' => $this->started_at->toDateTimeString(),
            'duration' => $delivery->duration,
            'extra_minute' => $this->extra_minute,
            'calculated_expires_at' => $this->started_at->addMinutes($delivery->duration + $this->extra_minute)->toDateTimeString()
        ]);

        return $this->started_at->addMinutes($delivery->duration + $this->extra_minute);
    }

    /**
     * Determine answered question completion.
     *
     * @param Item $item
     *
     * @return int
     */
    public function checkAnswerStatusFromItem(Item $item): int
    {
        if ($this->countAnsweredQuestionFromItem($item) >= $item->questions->count()) {
            return 1; // completed all question
        }

        if ($this->countAnsweredQuestionFromItem($item) > 0) {
            return 2; // answered some question but not all
        }

        return 0; // none answered
    }

    public function countAnsweredQuestionFromItem(Item $item): int
    {
        $questions = $item->questions->keyBy('id')->keys()->all();
        $answered = [];

        foreach ($questions as $questionId) {
            if ($this->hasAnswerFor($questionId)) {
                $answered[] = $questionId;
            }
        }

        return count($answered);
    }

    /**
     * Get answer for a question.
     *
     * @param $questionId
     *
     * @return string|null
     */
    public function getAnswerFor($questionId): ?string
    {
        $collection = ($this->questions instanceof Collection) ? $this->questions->keyBy('id') : new Collection();

        if ($collection->get($questionId, null) instanceof Question) {
            return $collection->get($questionId)->pivot->answer;
        }

        return null;
    }

    public function getQuestionById($id)
    {
        return $this->questions->first(function ($question, $key) use ($id) {
            return (string) $question->id === (string) $id;
        });
    }

    /**
     * Check if the question has an answer for this question.
     *
     * @param $questionId
     *
     * @return bool
     */
    public function hasAnswerFor($questionId): bool
    {
        return ! is_null($this->getAnswerFor($questionId));
    }
}
