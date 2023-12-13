<?php

namespace App\Providers;

use App\Services\Tracker\Contracts\TrackerServiceInterface;
use App\Services\Tracker\LinkeTrackerService;
use App\Services\Tracker\TrackerService;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local', 'staging')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        $this->app->singleton(
            TrackerServiceInterface::class,
            function (Application $app) {
                $client = Http::withOptions(
                    [
                        'base_uri' => config('services.tracker.url'),
                        'timeout' => config('services.tracker.timeout', 30),
                        'connect_timeout' => config('services.tracker.connect_timeout', 2),
                        'verify' => $app->isProduction(),
                    ])
                    ->withQueryParameters([
                        'user' => config('services.tracker.user'),
                        'token' => config('services.tracker.token'),
                    ])
                    ->acceptJson();

                return new LinkeTrackerService($client);
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
