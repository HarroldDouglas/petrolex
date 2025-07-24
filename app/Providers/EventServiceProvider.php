<?php

namespace App\Providers;

use App\Events\DistributionCenterUpdatedEvent;
use App\Events\UserDeletedEvent;
use App\Events\UserUpdatedEvent;
use App\Listeners\LogDistributionCenterUpdated;
use App\Listeners\LogUserDeleted;
use App\Listeners\LogUserUpdated;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        UserUpdatedEvent::class => [
            LogUserUpdated::class,
        ],
        UserDeletedEvent::class => [
            LogUserDeleted::class,
        ],
        DistributionCenterUpdatedEvent::class => [
            LogDistributionCenterUpdated::class,
        ],
        \App\Events\OrderStatusChanged::class => [
            \App\Listeners\Order\SendOrderStatusChangedNotificationToCustomer::class,
            \App\Listeners\Order\SendOrderStatusChangedNotificationToDeliveryPerson::class,
            \App\Listeners\Order\SendOrderStatusChangedNotificationToManager::class,
        ],

    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void {}
}
