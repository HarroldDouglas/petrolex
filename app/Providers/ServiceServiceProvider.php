<?php

namespace App\Providers;

use App\Services\Auth\AuthenticationService;
use App\Services\Auth\Contracts\AuthenticationServiceInterface;
use App\Services\Auth\Contracts\OtpServiceInterface;
use App\Services\Auth\OtpService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class ServiceServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        OtpServiceInterface::class => OtpService::class,
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
