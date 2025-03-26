<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use App\Models\Ticket;
use App\Models\Followup;
use App\Observers\TicketObserver;
use App\Observers\FollowupObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Inertia::share([
            'auth' => function () {
                return [
                    'user' => auth()->user() ? auth()->user()->load('roles') : null,
                ];
            },
        ]);

        Ticket::observe(TicketObserver::class);
        Followup::observe(FollowupObserver::class);
    }
}
