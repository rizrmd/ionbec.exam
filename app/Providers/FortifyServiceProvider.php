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
            
            // Find user without any scopes first
            $user = \App\Models\Accounts\User::withoutGlobalScopes()->where('username', $username)->first();
            
            if (!$user) {
                Log::warning('Login failed - user not found', [
                    'username' => $username,
                    'domain' => $domain
                ]);
                return null;
            }
            
            if (!\Hash::check($password, $user->password)) {
                Log::warning('Login failed - wrong password', [
                    'username' => $username,
                    'user_id' => $user->id,
                    'domain' => $domain
                ]);
                return null;
            }
            
            // For client domains, verify user belongs to the correct client
            $client = \App\Models\Client::findByDomain($domain);
            
            if ($client) {
                Log::info('Client domain detected', [
                    'domain' => $domain,
                    'client_id' => $client->id,
                    'user_client_id' => $user->client_id
                ]);
                
                if ($user->client_id !== $client->id) {
                    Log::warning('Login failed - user does not belong to client domain', [
                        'username' => $username,
                        'user_id' => $user->id,
                        'user_client_id' => $user->client_id,
                        'domain_client_id' => $client->id,
                        'domain' => $domain
                    ]);
                    return null; // User doesn't belong to this client
                }
            } else {
                Log::info('No client found for domain', ['domain' => $domain]);
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
