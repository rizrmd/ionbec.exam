<?php

namespace App\Models\Accounts;

use App\Models\Client;
use App\Models\Log\ActivityLog;
use App\Traits\BelongsToClient;
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
    use BelongsToClient {
        BelongsToClient::bootBelongsToClient as bootBelongsToClientTrait;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'name', 'email', 'password', 'client_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
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

    /**
     * Override the boot method to handle special cases for User model
     */
    protected static function bootBelongsToClient()
    {
        // Don't apply global scope during authentication
        if (!request()->is('login') && !request()->is('two-factor-challenge')) {
            static::addGlobalScope(new \App\Scopes\ClientScope);
        }

        // Automatically set client_id when creating
        static::creating(function ($model) {
            // Skip auto-assignment for root users
            if (!empty($model->roles) && $model->roles->contains('slug', 'root')) {
                return;
            }

            if (empty($model->client_id)) {
                $model->client_id = static::getCurrentClientId();
            }
        });
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $roleSlug): bool
    {
        // If roles are already loaded, check from the collection to avoid queries
        if ($this->relationLoaded('roles')) {
            return $this->roles->contains('slug', $roleSlug);
        }
        
        // Otherwise, query the database without triggering global scopes
        return $this->roles()->withoutGlobalScopes()->where('slug', $roleSlug)->exists();
    }

    /**
     * Check if user is root
     */
    public function isRoot(): bool
    {
        return $this->hasRole('root');
    }
}
