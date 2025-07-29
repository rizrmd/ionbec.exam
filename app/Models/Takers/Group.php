<?php

namespace App\Models\Takers;

use Carbon\Carbon;
use Carbon\Traits\Date;
use App\Models\Deliveries\Delivery;
use Illuminate\Database\Eloquent\Model;
use Veelasky\LaravelHashId\Eloquent\HashableId;

/**
 * Group model.
 *
 * @author      veelasky <veelasky@gmail.com>
 *
 * @property int id
 * @property string $name
 * @property string $description
 * @property int    $last_taker_code
 * @property Date   $closed_at
 */
class Group extends Model
{
    use HashableId;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'groups';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'closed_at'];

    protected $hidden = ['id'];
    protected $appends = ['hash', 'closed_in'];

    protected $casts = [
        'closed_at' => 'datetime:Y-m-d 23:59',
    ];

    public function getClosedInAttribute(): int
    {
        if ($this->closed_at) {
            return $this->closed_at->diffInDays(Carbon::now());
        }

        return 0;
    }

    /**
     * Define `belongsToMany` relationship with taker model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function takers()
    {
        return $this->belongsToMany(Taker::class, 'group_taker', 'group_id', 'taker_id')->withPivot(['taker_code']);
    }

    /**
     * Define `hasMany` relationship with Delivery model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'group_id', 'id');
    }
}
