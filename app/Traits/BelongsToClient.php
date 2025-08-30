<?php

namespace App\Traits;

use App\Models\Client;
use App\Scopes\ClientScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToClient
{
    /**
     * Boot the BelongsToClient trait for a model.
     *
     * @return void
     */
    protected static function bootBelongsToClient()
    {
        // Add global scope to filter by client
        static::addGlobalScope(new ClientScope);

        // Automatically set client_id when creating
        static::creating(function ($model) {
            if (empty($model->client_id)) {
                $model->client_id = static::getCurrentClientId();
            }
        });
    }

    /**
     * Define the relationship to the Client model.
     *
     * @return BelongsTo
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the current client ID from the application context.
     *
     * @return int|null
     */
    protected static function getCurrentClientId(): ?int
    {
        // First, check if we have a client in the app container
        if (app()->has('current_client')) {
            return app('current_client')->id;
        }

        // Then check session
        if (session()->has('client_id')) {
            return session('client_id');
        }

        // Finally, check authenticated user
        if (Auth::check() && Auth::user()->client_id) {
            return Auth::user()->client_id;
        }

        return null;
    }

    /**
     * Scope a query to only include models for the current client.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForClient($query, $clientId = null)
    {
        if ($clientId) {
            return $query->where('client_id', $clientId);
        }

        $currentClientId = static::getCurrentClientId();
        
        if ($currentClientId) {
            return $query->where('client_id', $currentClientId);
        }

        return $query;
    }

    /**
     * Disable client scope for this query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithoutClientScope($query)
    {
        return $query->withoutGlobalScope(ClientScope::class);
    }
}