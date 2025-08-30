<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class ClientScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        // Don't apply scope during authentication to prevent loops
        if (!app()->has('current_client') && !session()->has('client_id')) {
            return;
        }

        $clientId = $this->getCurrentClientId();

        if ($clientId) {
            $builder->where($model->getTable() . '.client_id', $clientId);
        }
    }

    /**
     * Get the current client ID from various sources.
     *
     * @return int|null
     */
    protected function getCurrentClientId(): ?int
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
}