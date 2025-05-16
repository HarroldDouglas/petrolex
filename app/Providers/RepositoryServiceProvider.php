<?php

namespace App\Providers;

use App\Contracts\Repositories\TokenRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\TokenRepositoryEloquent;
use App\Repositories\Eloquent\UserEloquentRepository;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        UserRepositoryInterface::class => UserEloquentRepository::class,
        TokenRepositoryInterface::class => TokenRepositoryEloquent::class,
    ];

    /**
     * @return array<class-string>
     */
    public function provides(): array
    {
        return array_keys($this->bindings);
    }
}
