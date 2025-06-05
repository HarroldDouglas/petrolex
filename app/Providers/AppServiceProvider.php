<?php

namespace App\Providers;

use App\Enums\UserRole;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\StockMovementRepositoryInterface;
use App\Repositories\Eloquent\StockMovementRepository; // <--- THIS IS CORRECT

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(StockMovementRepositoryInterface::class, StockMovementRepository::class);
        // ... other bindings
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            if ($user && $user->role === UserRole::SUPER_ADMIN()) {
                return true; // Autorise TOUT
            }

            // Continue avec la logique normale
            return null;
        });
    }
}
