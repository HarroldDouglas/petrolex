<?php

namespace App\Providers;

use App\Contracts\Services\AuthenticationServiceInterface;
use App\Services\Auth\AuthenticationService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class ServiceServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        AuthenticationServiceInterface::class => AuthenticationService::class,
    ];

    /**
     * @return array<class-string>
     */
    public function provides(): array
    {
        return array_keys($this->bindings);
    }
}
