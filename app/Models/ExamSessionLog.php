<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamSessionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'attempt_id',
        'session_key',
        'tab_count',
        'ip_address',
        'country',
        'city',
        'isp',
        'user_agent',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship with attempt
     */
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Attempts\Attempt::class);
    }

    /**
     * Scope for suspicious logs (multiple tabs)
     */
    public function scopeSuspicious($query)
    {
        return $query->where('tab_count', '>', 1);
    }

    /**
     * Scope for today's logs
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for IP address search
     */
    public function scopeByIp($query, $ipAddress)
    {
        return $query->where('ip_address', 'like', '%' . $ipAddress . '%');
    }

    /**
     * Scope for session key
     */
    public function scopeBySessionKey($query, $sessionKey)
    {
        return $query->where('session_key', $sessionKey);
    }

    /**
     * Check if this log shows suspicious activity
     */
    public function isSuspicious(): bool
    {
        return $this->tab_count > 1;
    }

    /**
     * Get formatted location
     */
    public function getFormattedLocationAttribute(): string
    {
        if ($this->city && $this->country) {
            return "{$this->city}, {$this->country}";
        } elseif ($this->country) {
            return $this->country;
        } else {
            return 'Unknown';
        }
    }

    /**
     * Get truncated user agent
     */
    public function getTruncatedUserAgentAttribute(): string
    {
        if (!$this->user_agent) {
            return 'Unknown';
        }

        return strlen($this->user_agent) > 100
            ? substr($this->user_agent, 0, 100) . '...'
            : $this->user_agent;
    }

    /**
     * Get tab count badge color
     */
    public function getTabCountColorAttribute(): string
    {
        if ($this->tab_count === 1) {
            return 'bg-green-100 text-green-800';
        } elseif ($this->tab_count === 2) {
            return 'bg-yellow-100 text-yellow-800';
        } else {
            return 'bg-red-100 text-red-800';
        }
    }

    /**
     * Get session information
     */
    public function getSessionInfoAttribute(): array
    {
        return [
            'session_key' => $this->session_key,
            'tab_count' => $this->tab_count,
            'duration' => $this->created_at->diffForHumans(),
            'is_suspicious' => $this->isSuspicious(),
        ];
    }
}