<?php

namespace App\Providers;

use App\Repositories\Contracts\BottleMovementRepositoryInterface;
use App\Repositories\Contracts\BottleRepositoryInterface;
use App\Repositories\Contracts\BottleTypeRepositoryInterface;
use App\Repositories\Contracts\DistributionCenterRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\TokenRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\BottleMovementRepository;
use App\Repositories\Eloquent\BottleRepository;
use App\Repositories\Eloquent\BottleTypeRepository;
use App\Repositories\Eloquent\DistributionCenterRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\TokenRepositoryEloquent;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        UserRepositoryInterface::class => UserRepository::class,
        OrderRepositoryInterface::class => OrderRepository::class,
        TokenRepositoryInterface::class => TokenRepositoryEloquent::class,
        DistributionCenterRepositoryInterface::class => DistributionCenterRepository::class,
        ProductRepositoryInterface::class => ProductRepository::class,
        BottleRepositoryInterface::class => BottleRepository::class,
        BottleMovementRepositoryInterface::class => BottleMovementRepository::class,
        BottleTypeRepositoryInterface::class => BottleTypeRepository::class,
    ];

    /**
     * @return array<class-string>
     */
    public function provides(): array
    {
        return array_keys($this->bindings);
    }
}
