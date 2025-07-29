<?php

namespace App\Models\Takers;

use App\Models\Attempts\Attempt;
use Illuminate\Support\Facades\DB;
use App\Models\Deliveries\Delivery;
use Illuminate\Database\Eloquent\Relations;
use Veelasky\LaravelHashId\Eloquent\HashableId;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Taker model.
 *
 * @author      veelasky <veelasky@gmail.com>
 *
 * @property int    $id
 * @property string $name
 * @property string $reg
 * @property string $email
 * @property string $password
 * @property bool   $is_verified
 */
class Taker extends Authenticatable
{
    use HashableId;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'takers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'reg', 'email', 'password', 'is_verified'];

    protected $hidden = ['id'];
    protected $appends = ['hash'];

    /**
     * Define `belongsToMany` relationship with Group Model.
     *
     * @return Relations\BelongsToMany
     */
    public function groups(): Relations\BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_taker', 'taker_id', 'group_id')->withPivot(['taker_code']);
    }

    public function attempts(): Relations\HasMany
    {
        return $this->hasMany(Attempt::class, 'attempted_by', 'id');
    }

    public function registerData(): Relations\HasOne
    {
        return $this->hasOne(RegisterData::class, 'taker_id');
    }

    /**
     * Define `belongsToMany` relationship with Taker model.
     *
     * @return Relations\BelongsToMany
     */
    public function deliveries(): Relations\BelongsToMany
    {
        return $this->belongsToMany(Delivery::class, 'delivery_taker', 'taker_id', 'delivery_id')->withPivot(['token', 'is_login']);
    }

    public function hasGroup($id)
    {
        return $this->groups->contains(function ($group, $key) use ($id) {
            return $group->id == $id;
        });
    }

    public function loadTakerCodeOf(Delivery $delivery)
    {
        $data = DB::table('group_taker')
            ->where('group_id', $delivery->group_id)
            ->where('taker_id', $this->attributes['id'])
            ->first();
        $this->taker_code = $delivery->group->code.'-'.$data?->taker_code;
    }

    public function loadAttemptHashOf(Delivery $delivery)
    {
        $attempt = $this->attempts()->where('delivery_id', $delivery->id)->first();
        if (! is_null($attempt)) {
            $this->attempt_hash = $attempt->hash;
        }
    }
}
