<?php

namespace App\Models\Accounts;

use App\Models\Log\ActivityLog;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Jetstream\HasProfilePhoto;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Veelasky\LaravelHashId\Eloquent\HashableId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property string $password
 */
class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    use HasFactory;
    use HasProfilePhoto;
    use HasApiTokens;
    use TwoFactorAuthenticatable;
    use HashableId;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'id',
    ];

    protected $appends = ['hash'];

    public function roles(): Relations\BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Define `morphMany` relationship with Attachment model.
     *
     * @return Relations\MorphMany
     */
    public function logs(): Relations\morphMany
    {
        return $this->morphMany(ActivityLog::class, 'causer');
    }
}
