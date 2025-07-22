<?php

namespace App\Providers;

use App\Repositories\Contracts\AccessoryRepositoryInterface;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\BottleMovementRepositoryInterface;
use App\Repositories\Contracts\BottleRepositoryInterface;
use App\Repositories\Contracts\BottleTypeRepositoryInterface;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\Contracts\DistributionCenterRepositoryInterface;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductCategoryCityPriceRepositoryInterface;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use App\Repositories\Contracts\SupplierDeliveryRepositoryInterface;
use App\Repositories\Contracts\TokenRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\AccessoryRepository;
use App\Repositories\Eloquent\BaseEloquentRepository;
use App\Repositories\Eloquent\BottleMovementRepository;
use App\Repositories\Eloquent\BottleRepository;
use App\Repositories\Eloquent\BottleTypeRepository;
use App\Repositories\Eloquent\CustomerRepository;
use App\Repositories\Eloquent\DistributionCenterRepository;
use App\Repositories\Eloquent\NotificationRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\ProductCategoryRepository;
use App\Repositories\Eloquent\SupplierDeliveryRepository;
use App\Repositories\Eloquent\TokenRepositoryEloquent;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Geography\EloquentGeographicRepository;
use App\Repositories\Geography\GeographyRepositoryInterface;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        BaseRepositoryInterface::class => BaseEloquentRepository::class,
        CustomerRepositoryInterface::class => CustomerRepository::class,
        ProductCategoryRepositoryInterface::class => ProductCategoryRepository::class,
        UserRepositoryInterface::class => UserRepository::class,
        \App\Repositories\Contracts\OrderRepositoryInterface::class => \App\Repositories\Eloquent\OrderRepository::class,
        \App\Repositories\Contracts\TokenRepositoryInterface::class => \App\Repositories\Eloquent\TokenRepositoryEloquent::class,
        \App\Repositories\Contracts\DistributionCenterRepositoryInterface::class => \App\Repositories\Eloquent\DistributionCenterRepository::class,
        \App\Repositories\Contracts\AccessoryRepositoryInterface::class => \App\Repositories\Eloquent\AccessoryRepository::class,
        \App\Repositories\Contracts\BottleRepositoryInterface::class => \App\Repositories\Eloquent\BottleRepository::class,
        \App\Repositories\Contracts\BottleMovementRepositoryInterface::class => \App\Repositories\Eloquent\BottleMovementRepository::class,
        \App\Repositories\Contracts\BottleTypeRepositoryInterface::class => \App\Repositories\Eloquent\BottleTypeRepository::class,
        \App\Repositories\Contracts\SupplierDeliveryRepositoryInterface::class => \App\Repositories\Eloquent\SupplierDeliveryRepository::class,
        \App\Repositories\Contracts\OrderBottleScanRepositoryInterface::class => \App\Repositories\Eloquent\OrderBottleScanRepository::class,
        \App\Repositories\Contracts\NotificationRepositoryInterface::class => \App\Repositories\Eloquent\NotificationRepository::class,
        \App\Repositories\Contracts\ProductCategoryCityPriceRepositoryInterface::class => \App\Repositories\ProductCategoryCityPriceRepository::class,
        \App\Repositories\Geography\GeographyRepositoryInterface::class => \App\Repositories\Geography\EloquentGeographicRepository::class,
    ];

    /**
     * @return array<class-string>
     */
    public function provides(): array
    {
        return array_keys($this->bindings);
    }
}
