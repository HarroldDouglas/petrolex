<?php

namespace App\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        \App\Repositories\Contracts\UserRepositoryInterface::class => \App\Repositories\Eloquent\UserRepository::class,
        \App\Repositories\Contracts\OrderRepositoryInterface::class => \App\Repositories\Eloquent\OrderRepository::class,
        \App\Repositories\Contracts\TokenRepositoryInterface::class => \App\Repositories\Eloquent\TokenRepositoryEloquent::class,
        \App\Repositories\Contracts\DistributionCenterRepositoryInterface::class => \App\Repositories\Eloquent\DistributionCenterRepository::class,
        \App\Repositories\Contracts\AccessoryRepositoryInterface::class => \App\Repositories\Eloquent\AccessoryRepository::class,
        \App\Repositories\Contracts\BottleRepositoryInterface::class => \App\Repositories\Eloquent\BottleRepository::class,
        \App\Repositories\Contracts\BottleMovementRepositoryInterface::class => \App\Repositories\Eloquent\BottleMovementRepository::class,
        \App\Repositories\Contracts\BottleTypeRepositoryInterface::class => \App\Repositories\Eloquent\BottleTypeRepository::class,
        \App\Repositories\Contracts\SupplierDeliveryRepositoryInterface::class => \App\Repositories\Eloquent\SupplierDeliveryRepository::class,
        \App\Repositories\Contracts\OrderBottleScanRepositoryInterface::class => \App\Repositories\Eloquent\OrderBottleScanRepository::class,
        \App\Repositories\Contracts\ProductCategoryRepositoryInterface::class => \App\Repositories\Eloquent\ProductCategoryRepository::class,
        \App\Repositories\Contracts\NotificationRepositoryInterface::class => \App\Repositories\Eloquent\NotificationRepository::class,
        \App\Repositories\Contracts\ProductCategoryCityPriceRepositoryInterface::class => \App\Repositories\ProductCategoryCityPriceRepository::class,
        \App\Repositories\Geography\GeographyRepositoryInterface::class => \App\Repositories\Geography\EloquentGeographicRepository::class,
        \App\Repositories\Contracts\CustomerRepositoryInterface::class => \App\Repositories\CustomerRepository::class,
    ];

    /**
     * @return array<class-string>
     */
    public function provides(): array
    {
        return array_keys($this->bindings);
    }
}
