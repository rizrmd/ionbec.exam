<?php

namespace App\Models\Attachments;

use App\Models\Accounts\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;

/**
 * Attachment model.
 *
 * @property mixed $id
 */
class Attachment extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attachments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'type',
        'uploaded_by',
        'path',
        'title',
        'mime',
        'description',
    ];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'url',
    ];

    /**
     * Define `belongsTo` relationship with User model.
     *
     * @return Relations\BelongsTo
     */
    public function uploader(): Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by', 'id');
    }

    /**
     * Get Url attribute mutator.
     *
     * @return string
     */
    public function getUrlAttribute(): string
    {
        return route('attachment.stream', $this->id);
    }
}
