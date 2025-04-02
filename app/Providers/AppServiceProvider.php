<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use App\Models\Ticket;
use App\Models\Followup;
use App\Observers\TicketObserver;
use App\Observers\FollowupObserver;
use App\Services\FileService;
use Illuminate\Support\Facades\Auth;
use App\Services\TicketFilterService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Регистрируем FileService в контейнере как синглтон
        $this->app->singleton(FileService::class, function ($app) {
            return new FileService();
        });

        $this->app->singleton(TicketFilterService::class, function ($app) {
            return new TicketFilterService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Inertia::share([
            'auth' => function () {
                return [
                    'user' => Auth::user() ? Auth::user()->load('roles') : null,
                ];
            },
        ]);

        Ticket::observe(TicketObserver::class);
        Followup::observe(FollowupObserver::class);
    }
}
