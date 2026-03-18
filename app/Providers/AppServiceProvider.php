<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register current_tenant_id as a callable that returns the tenant ID from the authenticated user
        $this->app->bind('current_tenant_id', function () {
            $user = auth()->user();
            if ($user) {
                if (isset($user->tenant_id) && $user->tenant_id) {
                    return $user->tenant_id;
                }
                if (method_exists($user, 'driver') && $user->driver) {
                    return $user->driver->tenant_id;
                }
                if (method_exists($user, 'guardian') && $user->guardian) {
                    return $user->guardian->tenant_id;
                }
            }
            return null;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
