<?php

namespace App\Providers;

use Laravel\Fortify\Fortify;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Support\ServiceProvider;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Support\Facades\Log;

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
            $domain = $request->getHost();
            
            Log::info('Login attempt', [
                'username' => $username,
                'domain' => $domain,
                'ip' => $request->ip()
            ]);
            
            // For client domains, find the correct user based on client_id
            $client = \App\Models\Client::findByDomain($domain);
            
            if ($client) {
                Log::info('Client domain detected', [
                    'domain' => $domain,
                    'client_id' => $client->id
                ]);
                
                // Find user with matching username AND client_id
                $user = \App\Models\Accounts\User::withoutGlobalScopes()
                    ->where('username', $username)
                    ->where('client_id', $client->id)
                    ->first();
            } else {
                Log::info('No client found for domain', ['domain' => $domain]);
                
                // For non-client domains, find user without client_id (global admin)
                $user = \App\Models\Accounts\User::withoutGlobalScopes()
                    ->where('username', $username)
                    ->whereNull('client_id')
                    ->first();
            }
            
            if (!$user) {
                Log::warning('Login failed - user not found', [
                    'username' => $username,
                    'domain' => $domain,
                    'expected_client_id' => $client?->id
                ]);
                return null;
            }
            
            if (!\Hash::check($password, $user->password)) {
                Log::warning('Login failed - wrong password', [
                    'username' => $username,
                    'user_id' => $user->id,
                    'user_client_id' => $user->client_id,
                    'domain' => $domain
                ]);
                return null;
            }
            
            Log::info('Login successful', [
                'username' => $username,
                'user_id' => $user->id,
                'domain' => $domain
            ]);
            
            return $user;
        });
    }
}
