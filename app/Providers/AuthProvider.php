<?php

namespace App\Providers;

use App\Models\Driver;
use App\Models\Guardian;
use App\Policies\DriverPolicy;
use App\Policies\GuardianPolicy;
use App\Services\Auth\DriverAuthService;
use App\Services\Auth\GuardianAuthService;
use App\Services\Auth\TokenService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register auth services for dependency injection
        $this->app->singleton(TokenService::class, function () {
            return new TokenService();
        });

        $this->app->singleton(DriverAuthService::class, function () {
            return new DriverAuthService(
                $this->app->make(TokenService::class)
            );
        });

        $this->app->singleton(GuardianAuthService::class, function () {
            return new GuardianAuthService(
                $this->app->make(TokenService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register policies
        Gate::policy(Driver::class, DriverPolicy::class);
        Gate::policy(Guardian::class, GuardianPolicy::class);
    }
}

