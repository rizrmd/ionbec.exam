<?php

namespace App\Models\Exams;

use Illuminate\Support\Str;
use App\Models\Attempts\Attempt;
use App\Models\Categories\Category;
use App\Models\Attachments\Attachment;
use Illuminate\Database\Eloquent\Model;
use App\Models\Attempts\AttemptQuestion;
use Illuminate\Database\Eloquent\Relations;
use Veelasky\LaravelHashId\Eloquent\HashableId;
use App\Providers\Question\Contracts\QuestionType;

/**
 * Question model.
 *
 * @property \Illuminate\Database\Eloquent\Collection             $categories
 * @property \Illuminate\Database\Eloquent\Collection             $answers
 * @property \App\Providers\Question\Types\BasicQuestionType|null $type
 * @property Item                                                 $item
 * @property string                                               $item_id
 *
 * @author      veelasky <veelasky@gmail.com>
 */
class Question extends Model
{
    use HashableId;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['item_id', 'type', 'question', 'score'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        //        'answers',
        //        'categories'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['hash', 'correctness', 'is_attempted'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_random' => 'boolean',
        'score' => 'integer',
    ];

    protected $hidden = [
        'id',
    ];

    /**
     * Define `belongsTo` relationship with Item.
     *
     * @return Relations\BelongsTo
     */
    public function item(): Relations\BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    /**
     * Define `hasMany` relationship with Answer model.
     *
     * @return Relations\HasMany
     */
    public function answers(): Relations\HasMany
    {
        return $this->hasMany(Answer::class, 'question_id', 'id');
    }

    /**
     * Define `morphToMany` relationship with Attachment model.
     *
     * @return Relations\MorphToMany
     */
    public function attachments(): Relations\MorphToMany
    {
        return $this->morphToMany(Attachment::class, 'attachable', 'attachables');
    }

    public function attempts(): Relations\BelongsToMany
    {
        return $this->belongsToMany(Attempt::class, 'attempt_question', 'question_id', 'attempt_id')->withPivot([
            'answer_hash', 'answer', 'is_correct', 'score',
        ]);
    }

    /**
     * Define `belongsToMany` relationship with Category model.
     *
     * @return Relations\BelongsToMany
     */
    public function categories(): Relations\BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_question', 'question_id', 'category_id');
    }

    public function attemptQuestions(): Relations\HasMany
    {
        return $this->hasMany(AttemptQuestion::class, 'question_id');
    }

    public function getCorrectnessAttribute(): float|int
    {
        $query = AttemptQuestion::query()->where('question_id', $this->id);
        $total = $query->clone()->count();
        $correct = $query->where('is_correct', true)->count();
        $correctness = 0;
        if ($total >= 1) {
            $correctness = round($correct * 100 / $total, 2);
        }

        return $correctness;
    }

    public function getIsAttemptedAttribute(): bool
    {
        return $this->attemptQuestions()->count() > 0;
    }

    public function getDiseaseGroupAttribute()
    {
        return $this->categories->first(function ($category, $key) {
            return 'disease-group' === $category->slug;
        });
    }

    public function getRegionGroupAttribute()
    {
        return $this->categories->first(function ($category, $key) {
            return 'region-group' === $category->slug;
        });
    }

    public function getSpecificPartAttribute()
    {
        return $this->categories->first(function ($category, $key) {
            return 'specific-part' === $category->slug;
        });
    }

    public function getTypicalGroupAttribute()
    {
        return $this->categories->first(function ($category, $key) {
            return 'typical-group' === $category->slug;
        });
    }

    public function getCorrectAnswerAttribute(): \Illuminate\Support\Collection
    {
        $filtered = $this->answers->filter(function ($answer, $key) {
            return $answer->is_correct_answer;
        });

        return $filtered->keyBy('id')->keys();
    }

    public function getExcerptAttribute(): string
    {
        return Str::words(strip_tags($this->getOriginal('question')), 10, '...');
    }

    /**
     * Get Type attribute mutator.
     *
     * @param $type
     *
     * @return QuestionType|null
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getTypeAttribute($type): ?QuestionType
    {
        return app('exam.questions')->get($type);
    }

    public function totalAttempts(): int
    {
        return $this->attempts()->get()->count();
    }

    public function totalCorrectAnswer(): int
    {
        return $this->attempts()->wherePivot('is_correct', '=', 1)->get()->count();
    }
}
