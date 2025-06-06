<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\StockMovementRepositoryInterface;
use App\Repositories\Eloquent\StockMovementRepository;

class StockMovementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            StockMovementRepositoryInterface::class,
            StockMovementRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
