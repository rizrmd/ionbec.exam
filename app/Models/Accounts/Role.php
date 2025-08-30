<?php

namespace App\Models\Accounts;

use App\Models\Client;
use App\Traits\BelongsToClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Role extends Model
{
    use HasFactory, BelongsToClient;

    const ROOT = 'root';
    const CLIENT_ADMIN = 'administrator'; // Match legacy slug
    const SCORER = 'scorer';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id'
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function isRoot(): bool
    {
        return $this->slug === self::ROOT;
    }

    public function isClientAdmin(): bool
    {
        return $this->slug === self::CLIENT_ADMIN;
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isRoot()) {
            return true; // Root has all permissions
        }

        // Legacy database doesn't have permissions column
        // Define permissions based on role slug
        $rolePermissions = $this->getLegacyPermissions();
        return in_array($permission, $rolePermissions);
    }

    private function getLegacyPermissions(): array
    {
        return match($this->slug) {
            'administrator' => [
                'manage_users',
                'manage_exams', 
                'manage_questions',
                'manage_deliveries',
                'manage_takers',
                'view_reports',
                'client_settings'
            ],
            'scorer' => [
                'score_exams',
                'view_submissions', 
                'manage_scoring',
                'view_reports'
            ],
            default => []
        };
    }
}
