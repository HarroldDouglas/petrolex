<?php

// app/Providers/GeographyServiceProvider.php

namespace App\Providers;

use App\Services\Geography\CachedFreeGeographyService;
use App\Services\Geography\FreeGeographyService;
use App\Services\Geography\GeographyServiceInterface;
use App\Services\Shared\Cache\CacheServiceInterface;
use Illuminate\Support\ServiceProvider;

class GeographyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(GeographyServiceInterface::class, function ($app) {
            return new CachedFreeGeographyService(
                $app->make(FreeGeographyService::class),
                $app->make(CacheServiceInterface::class),
                (int) config('geography.cache_ttl', 86400)
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
