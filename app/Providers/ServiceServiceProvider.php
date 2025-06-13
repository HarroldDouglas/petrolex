<?php

namespace App\Providers;

use App\Services\Auth\AuthenticationService;
use App\Services\Auth\Contracts\AuthenticationServiceInterface;
use App\Services\Auth\Contracts\OtpServiceInterface;
use App\Services\Auth\OtpService;
use App\Services\BaseService;
use App\Services\BaseServiceInterface;
use App\Services\Geography\GeographyServiceInterface;
use App\Services\Geography\StaticGeographyService;
use App\Services\Permissions\PermissionService;
use App\Services\Permissions\PermissionServiceInterface;
use App\Services\Shared\Media\MediaService;
use App\Services\Shared\Media\MediaServiceInterface;
use App\Services\SMS\SmsServiceInterface;
use App\Services\SMS\TwilioService;
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
        SmsServiceInterface::class => TwilioService::class,
        PermissionServiceInterface::class => PermissionService::class,
        BaseServiceInterface::class => BaseService::class,
        GeographyServiceInterface::class => StaticGeographyService::class,
        MediaServiceInterface::class => MediaService::class,
    ];

    /**
     * @return array<class-string>
     */
    public function provides(): array
    {
        return array_keys($this->bindings);
    }
}
