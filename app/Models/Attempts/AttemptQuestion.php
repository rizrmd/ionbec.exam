<?php

namespace App\Models\Attempts;

use App\Models\Exams\Question;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string   $answer_id
 * @property bool     $is_correct
 * @property Question $question
 * @property int      $score
 */
class AttemptQuestion extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attempt_question';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'attempt_id',
        'question_id',
        'answer_id',
        'answer_hash',
        'answer',
        'is_correct',
        'score',
    ];

    protected $casts = [
        'score' => 'double',
    ];

    public function attempt(): Relations\belongsTo
    {
        return $this->belongsTo(Attempt::class, 'attempt_id');
    }

    public function question(): Relations\belongsTo
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
}
