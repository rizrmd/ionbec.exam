<?php

namespace App\Models\Exams;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Veelasky\LaravelHashId\Eloquent\HashableId;

/**
 * Answer model.
 *
 * @author      veelasky <veelasky@gmail.com>
 */
class Answer extends Model
{
    use HashableId;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'answers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['question_id', 'answer', 'is_correct_answer'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_correct_answer' => 'boolean',
    ];

    protected $appends = [
        'hash',
    ];

    protected $hidden = [
        'id',
    ];

    /**
     * Define `belongsTo` relation with Question table.
     *
     * @return Relations\BelongsTo
     */
    public function question(): Relations\BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id', 'id');
    }
}
