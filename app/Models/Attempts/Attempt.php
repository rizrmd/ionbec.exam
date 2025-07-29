<?php

namespace App\Models\Attempts;

use Carbon\Carbon;
use App\Models\Exams\Exam;
use App\Models\Exams\Item;
use App\Models\Takers\Taker;
use App\Models\Exams\Question;
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
        return $this->belongsTo(Taker::class, 'attempted_by', 'id');
    }

    /**
     * Define `belongsTo` relationship with Exam model.
     *
     * @return Relations\BelongsTo
     */
    public function exam(): Relations\BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id', 'id');
    }

    /**
     * define `belongsTo` relationship with Delivery model.
     *
     * @return Relations\BelongsTo
     */
    public function delivery(): Relations\BelongsTo
    {
        return $this->belongsTo(Delivery::class, 'delivery_id', 'id');
    }

    public function takerCode(): Attribute
    {
        return new Attribute(
            get: function () {
                $this->load('delivery.group');
                $data = DB::table('group_taker')
                    ->where('group_id', $this->delivery->group_id)
                    ->where('taker_id', $this->attempted_by)
                    ->first();

                return $this->delivery->group->code.'-'.$data?->taker_code;
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
        if ($this->delivery->is_anytime) {
            return $this->started_at->addMinutes($this->delivery->duration + $this->extra_minute);
        }

        return $this->delivery->ended_at->addMinutes($this->extra_minute);
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
