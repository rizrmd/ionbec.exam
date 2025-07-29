<?php

namespace App\Models\Exams;

use Illuminate\Support\Collection;
use App\Models\Deliveries\Delivery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Veelasky\LaravelHashId\Eloquent\HashableId;

/**
 * Exam model.
 *
 * @property Collection $items
 * @property string     $name
 * @property bool       $is_mcq
 */
class Exam extends Model
{
    use HashableId;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'exams';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['code', 'name', 'description', 'options', 'is_mcq', 'is_random', 'is_interview'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    //    protected $with = ['items'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'options' => 'object',
        'is_random' => 'boolean',
        'is_mcq' => 'boolean',
        'is_interview' => 'boolean',
    ];

    protected $appends = [
        'hash',
        'items_count',
    ];

    protected $hidden = [
        'id',
    ];

    /**
     * Get `questions` attribute mutator.
     *
     * @return Collection
     */
    public function getQuestionsAttribute(): Collection
    {
        $collection = new Collection();

        foreach ($this->items as $item) {
            foreach ($item->questions as $question) {
                $collection->put($question->id, $question);
            }
        }

        return $collection;
    }

    public function getItemsCountAttribute(): int
    {
        return $this->items()->count();
    }

    /**
     * Define `belongsToMany` relationship with items model.
     *
     * @return Relations\BelongsToMany
     */
    public function items(): Relations\BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'exam_item', 'exam_id', 'item_id')->withPivot(['order']);
    }

    /**
     * Determine how many question actually are in this exam.
     *
     * @return int
     */
    public function totalQuestions(): int
    {
        return Question::query()->whereIn('item_id', $this->items()->pluck('id'))->count();
    }

    /**
     * Define `hasMany` relationship with deliveries model.
     *
     * @return Relations\HasMany
     */
    public function deliveries(): Relations\HasMany
    {
        return $this->hasMany(Delivery::class, 'exam_id', 'id');
    }
}
