<?php

namespace App\Providers;

use Laravel\Fortify\Fortify;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Support\ServiceProvider;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use App\Http\Responses\LoginResponse;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Register custom login response to handle Inertia redirects properly
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
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
        
        // Configure Inertia views for authentication
        Fortify::loginView(function () {
            $client = request()->attributes->get('client');

            return \Inertia\Inertia::render('Auth/Login', [
                'canResetPassword' => \Illuminate\Support\Facades\Route::has('password.request'),
                'status' => session('status'),
                'client' => $client ? [
                    'name' => $client->name,
                    'logo_url' => $client->logo_url,
                ] : null,
            ]);
        });
        
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

                // Find user with matching username OR email AND client_id
                $user = \App\Models\Accounts\User::withoutGlobalScopes()
                    ->where(function($query) use ($username) {
                        $query->where('username', $username)
                              ->orWhere('email', $username);
                    })
                    ->where('client_id', $client->id)
                    ->first();
            } else {
                Log::info('No client found for domain', ['domain' => $domain]);

                // For non-client domains, find user without client_id (global admin)
                $user = \App\Models\Accounts\User::withoutGlobalScopes()
                    ->where(function($query) use ($username) {
                        $query->where('username', $username)
                              ->orWhere('email', $username);
                    })
                    ->whereNull('client_id')
                    ->first();
            }

            if (!$user) {
                Log::warning('Login failed - user not found', [
                    'username' => $username,
                    'domain' => $domain,
                    'expected_client_id' => $client?->id
                ]);

                // Use session flash message for user not found
                session()->flash('error', 'These credentials do not match our records.');
                return false;
            }

            if (!\Hash::check($password, $user->password)) {
                Log::warning('Login failed - wrong password', [
                    'username' => $username,
                    'user_id' => $user->id,
                    'user_client_id' => $user->client_id,
                    'domain' => $domain
                ]);

                // Use session flash message for wrong password
                session()->flash('error', 'These credentials do not match our records.');
                return false;
            }

            Log::info('Login successful', [
                'username' => $username,
                'user_id' => $user->id,
                'user_client_id' => $user->client_id,
                'domain' => $domain,
                'is_client_domain' => $client !== null,
                'client_id' => $client?->id,
            ]);

            return $user;
        });
    }
}
