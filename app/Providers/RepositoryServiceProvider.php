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
        \App\Repositories\Contracts\CountryRepositoryInterface::class => \App\Repositories\Eloquent\CountryRepository::class,
        \App\Repositories\Contracts\BaseRepositoryInterface::class => \App\Repositories\Eloquent\BaseEloquentRepository::class,
        \App\Repositories\Contracts\CustomerRepositoryInterface::class => \App\Repositories\Eloquent\CustomerRepository::class,
        \App\Repositories\Contracts\ProductCategoryRepositoryInterface::class => \App\Repositories\Eloquent\ProductCategoryRepository::class,
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
        \App\Repositories\Contracts\NotificationRepositoryInterface::class => \App\Repositories\Eloquent\NotificationRepository::class,
        \App\Repositories\Contracts\ProductCategoryCityPriceRepositoryInterface::class => \App\Repositories\Eloquent\ProductCategoryCityPriceRepository::class,
        \App\Repositories\Geography\GeographyRepositoryInterface::class => \App\Repositories\Geography\EloquentGeographicRepository::class,
        \App\Repositories\Contracts\DeliveryPersonRepositoryInterface::class => \App\Repositories\Eloquent\DeliveryPersonRepository::class,
        \App\Repositories\Contracts\DeliveryTrackingRepositoryInterface::class => \App\Repositories\Eloquent\DeliveryTrackingRepository::class,
        \App\Repositories\Contracts\ProductRepositoryInterface::class => \App\Repositories\Eloquent\ProductRepository::class,
        \App\Repositories\Contracts\CityRepositoryInterface::class => \App\Repositories\Eloquent\CityRepository::class,
        \App\Repositories\Contracts\NeighborhoodRepositoryInterface::class => \App\Repositories\Eloquent\NeighborhoodRepository::class,
        \App\Repositories\Contracts\MunicipalityRepositoryInterface::class => \App\Repositories\Eloquent\MunicipalityRepository::class,
        \App\Repositories\Contracts\RoleRepositoryInterface::class => \App\Repositories\Eloquent\RoleRepository::class,
        \App\Repositories\Contracts\PermissionRepositoryInterface::class => \App\Repositories\Eloquent\PermissionRepository::class,
        \App\Repositories\Contracts\OrderPaymentRepositoryInterface::class => \App\Repositories\Eloquent\OrderPaymentRepository::class,
    ];

    /**
     * @return array<class-string>
     */
    public function provides(): array
    {
        return array_keys($this->bindings);
    }
}
