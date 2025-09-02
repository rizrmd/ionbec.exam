<?php

namespace App\Providers;

use Laravel\Fortify\Fortify;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Support\ServiceProvider;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        
        // Custom authentication to handle multi-tenant login
        Fortify::authenticateUsing(function ($request) {
            $username = $request->input('username');
            $password = $request->input('password');
            
            // Find user without any scopes first
            $user = \App\Models\Accounts\User::withoutGlobalScopes()->where('username', $username)->first();
            
            if (!$user || !\Hash::check($password, $user->password)) {
                return null;
            }
            
            // For client domains, verify user belongs to the correct client
            $domain = $request->getHost();
            $client = \App\Models\Client::findByDomain($domain);
            
            if ($client && $user->client_id !== $client->id) {
                return null; // User doesn't belong to this client
            }
            
            return $user;
        });
    }
}
