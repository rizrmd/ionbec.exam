<?php

namespace App\Models\Categories;

use App\Models\Exams\Item;
use App\Models\Exams\Question;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Veelasky\LaravelHashId\Eloquent\HashableId;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Category model.
 *
 * @property string $type
 * @property string $name
 * @property string $description
 */
class Category extends Model
{
    use HashableId;

    protected $table = 'categories';

    protected $fillable = ['name', 'description', 'type', 'code'];

    protected $appends = ['slug', 'question_total', 'hash'];

    protected $hidden = ['id'];

    public function items(): Relations\BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'category_item', 'category_id', 'item_id');
    }

    public function questions(): Relations\BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'category_question', 'category_id', 'question_id');
    }

    public function questionTotal(): Attribute
    {
        return new Attribute(
            get: fn () => $this->questions()->count(),
        );
    }

    public function slug(): Attribute
    {
        return new Attribute(
            get: fn () => $this->getOriginal('type'),
        );
    }
}
