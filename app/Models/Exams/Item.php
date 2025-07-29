<?php

namespace App\Models\Exams;

use Illuminate\Support\Str;
use App\Models\Categories\Category;
use App\Knowledge\Exam\Item\ItemType;
use App\Models\Attachments\Attachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Veelasky\LaravelHashId\Eloquent\HashableId;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Item Model.
 *
 * @property \Illuminate\Database\Eloquent\Collection $questions
 * @property bool                                     $is_vignette
 * @property ItemType                                 $type
 */
class Item extends Model
{
    use HashableId;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'content', 'type'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'hash',
        'item_type',
    ];

    protected $hidden = [
        'id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_vignette' => 'boolean',
        'is_random' => 'boolean',
        'type' => ItemType::class,
    ];

    /**
     * Define `hasMany` relationship with Question model.
     *
     * @return Relations\HasMany
     */
    public function questions(): Relations\HasMany
    {
        return $this->hasMany(Question::class, 'item_id', 'id');
    }

    /**
     * Define `belongsToMany` relationship with Exam model.
     *
     * @return Relations\BelongsToMany
     */
    public function exams(): Relations\BelongsToMany
    {
        return $this->belongsToMany(Exam::class, 'exam_item', 'item_id', 'exam_id')->withPivot(['order']);
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

    public function getExcerptAttribute(): string
    {
        if ($this->is_vignette) {
            return Str::words(strip_tags($this->getOriginal('content')), 10, '...');
        }

        if ($this->questions->count() > 0) {
            return $this->questions->first()->excerpt;
        }

        return '....';
    }

    public function categories(): Relations\BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function itemType(): Attribute
    {
        return new Attribute(
            get: fn () => $this->type->jsonSerialize(),
        );
    }
}
