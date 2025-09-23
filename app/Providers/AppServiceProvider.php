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
        $this->app->bind(
            \App\Repositories\Contracts\CountryRepositoryInterface::class,
            \App\Repositories\Eloquent\CountryRepository::class
        );
        $this->app->singleton(
            \App\Services\Geography\CountryService::class,
            function ($app) {
                return new \App\Services\Geography\CountryService(
                    $app->make(\App\Repositories\Contracts\CountryRepositoryInterface::class)
                );
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {}
}
