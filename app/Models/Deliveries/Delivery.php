<?php

namespace App\Models\Deliveries;

use Carbon\Carbon;
use App\Models\Exams\Exam;
use App\Models\Takers\Group;
use App\Models\Takers\Taker;
use App\Scopes\DeliveryScope;
use App\Models\Log\ActivityLog;
use App\Models\Attempts\Attempt;
use App\Attributes\DeliveryAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Veelasky\LaravelHashId\Eloquent\HashableId;

/**
 * Delivery model.
 *
 * @property int                                      $exam_id
 * @property int                                      $group_id
 * @property string                                   $name
 * @property string                                   $display_name
 * @property Carbon                                   $ended_at
 * @property Carbon                                   $scheduled_at
 * @property string                                   $last_status
 * @property int                                      $duration
 * @property \Illuminate\Database\Eloquent\Collection $attempts
 * @property Exam                                     $exam
 * @property bool                                     $is_interview
 * @property bool                                     $is_anytime
 * @property bool                                     $is_finished
 * @property bool                                     $automatic_start
 */
class Delivery extends Model
{
    use HashableId;
    use DeliveryAttributes;
    use DeliveryScope;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'deliveries';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'exam_id', 'group_id', 'name', 'is_anytime', 'scheduled_at', 'ended_at', 'duration', 'automatic_start',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'scheduled_at' => 'datetime:Y-m-d H:i',
        'is_anytime' => 'boolean',
        'automatic_start' => 'boolean',
        'duration' => 'integer',
    ];

    protected $appends = [
        'hash', 'status', 'takers_ready', 'takers_count', 'questions_count', 'is_interview',
    ];

    protected $hidden = ['id', 'exam_id', 'group_id'];

    public const STATUS_CREATED = 'Created';
    public const STATUS_SCHEDULED = 'Scheduled';
    public const STATUS_NOT_STARTED = 'Not Started';
    public const STATUS_ON_PROGRESS = 'On Progress';
    public const STATUS_OVERDUE = 'Overdue';
    public const STATUS_SCORING = 'Scoring';
    public const STATUS_FINISHED = 'Finished';

    protected static function booted()
    {
        static::retrieved(function (self $delivery) {
            $delivery->last_status = self::getStatus($delivery);
            $delivery->saveQuietly();
        });
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
     * Define `belongsTo` relationship with Group Model.
     *
     * @return Relations\BelongsTo
     */
    public function group(): Relations\BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }

    /**
     * Define `belongsToMany` relationship with Taker model.
     *
     * @return Relations\BelongsToMany
     */
    public function takers(): Relations\BelongsToMany
    {
        return $this->belongsToMany(Taker::class, 'delivery_taker', 'delivery_id', 'taker_id')->withPivot(['token', 'is_login']);
    }

    /**
     * Define `hasMany` relationship with Attempt model.
     *
     * @return Relations\HasMany
     */
    public function attempts(): Relations\HasMany
    {
        return $this->hasMany(Attempt::class, 'delivery_id', 'id');
    }

    /**
     * Define `morphMany` relationship with Attachment model.
     *
     * @return Relations\MorphMany
     */
    public function logs(): Relations\morphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }

    public function hasAttemptTaker($takerId)
    {
        return $this->attempts->contains(fn (Attempt $attempt) => (string) $takerId === (string) $attempt->attempted_by);
    }

    public function getAttemptByTakerId($takerId)
    {
        return $this->attempts->first(fn (Attempt $attempt) => (string) $takerId === (string) $attempt->attempted_by);
    }
}
