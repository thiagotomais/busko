<?php

namespace App\Providers;

use App\Events\DriverRegistered;
use App\Events\GuardianRegistered;
use App\Events\UserCreated;
use App\Events\UserLoggedIn;
use App\Listeners\LogUserActivity;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        UserCreated::class => [
            // Add listeners here as needed
        ],
        DriverRegistered::class => [
            // Add listeners here as needed
        ],
        GuardianRegistered::class => [
            // Add listeners here as needed
        ],
        UserLoggedIn::class => [
            LogUserActivity::class,
        ],
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Events will be dispatched in services
    }
}

