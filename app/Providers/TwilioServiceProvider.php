<?php

namespace App\Providers;

use App\Services\SMS\TwilioService;
use Illuminate\Support\ServiceProvider;

class TwilioServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('twilio', function ($app) {
            $config = config('services.twilio');

            return new TwilioService($config['sid'], $config['token'], $config['from']);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
