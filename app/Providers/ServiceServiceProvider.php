<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\DeliveryTrackingServiceInterface;
use App\Services\MapboxService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

final class ServiceServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        DeliveryTrackingServiceInterface::class => MapboxService::class,
    ];

    /**
     * @return array<class-string>
     */
    public function provides(): array
    {
        return array_keys($this->bindings);
    }
}
