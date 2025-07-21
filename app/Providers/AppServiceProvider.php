<?php

namespace App\Providers;

use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use App\Repositories\Eloquent\BaseEloquentRepository;
use App\Repositories\Eloquent\CustomerRepository;
use App\Repositories\Eloquent\ProductCategoryRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BaseRepositoryInterface::class, BaseEloquentRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(ProductCategoryRepositoryInterface::class, ProductCategoryRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register the OrderObserver
        \App\Models\Order::observe(\App\Observers\OrderObserver::class);
    }
}
