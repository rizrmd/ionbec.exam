<?php

namespace App\Models;

use App\Models\Accounts\User;
use App\Models\Exams\Exam;
use App\Models\Exams\Question;
use App\Models\Categories\Category;
use App\Models\Takers\Taker;
use App\Models\Takers\Group;
use App\Models\Deliveries\Delivery;
use App\Models\Attempts\Attempt;
use App\Services\TraefikDomainService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'domains',
        'is_active',
        'settings',
        'primary_contact_email',
        'primary_contact_phone',
        'notes'
    ];

    protected $casts = [
        'domains' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean'
    ];

    protected $appends = ['logo_url'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function takers(): HasMany
    {
        return $this->hasMany(Taker::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }

    public function hasDomain(string $domain): bool
    {
        return in_array($domain, $this->domains ?? []);
    }

    public static function findByDomain(string $domain): ?self
    {
        return static::where('is_active', true)
            ->where('domains', 'like', '%"' . $domain . '"%')
            ->first();
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) {
            return null;
        }
        
        if (filter_var($this->logo, FILTER_VALIDATE_URL)) {
            return $this->logo;
        }
        
        return asset('storage/' . $this->logo);
    }
    
    protected static function booted()
    {
        static::saved(function (Client $client) {
            if ($client->wasChanged(['domains', 'is_active'])) {
                app(TraefikDomainService::class)->updateMdxmConfig();
            }
        });
        
        static::deleted(function (Client $client) {
            app(TraefikDomainService::class)->updateMdxmConfig();
        });
    }
}
